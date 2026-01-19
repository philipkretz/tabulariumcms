<?php

namespace App\Service;

use App\Entity\IntelligentAgent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

/**
 * Service that executes AI agents with their configured tools
 */
class AgentExecutorService
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private AgentToolsService $toolsService,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
        private string $openaiApiKey
    ) {
    }

    /**
     * Execute an agent with given input
     */
    public function execute(IntelligentAgent $agent, string $userInput, array $context = []): array
    {
        $agent->incrementExecutionCount();
        $agent->setLastExecutedAt(new \DateTimeImmutable());

        try {
            // Prepare messages
            $messages = [
                [
                    'role' => 'system',
                    'content' => $agent->getSystemPrompt()
                ],
                [
                    'role' => 'user',
                    'content' => $userInput
                ]
            ];

            // Prepare tools in OpenAI format
            $tools = $this->prepareToolsForApi($agent);

            // Make API request
            $response = $this->callOpenAI(
                $agent->getModel() ?? 'gpt-4',
                $messages,
                $tools,
                $agent->getTemperature() ?? 0.7,
                $agent->getMaxTokens() ?? 2000
            );

            // Handle response
            if (isset($response['error'])) {
                $agent->incrementFailureCount();
                $this->entityManager->flush();
                return [
                    'success' => false,
                    'error' => $response['error']
                ];
            }

            $message = $response['choices'][0]['message'];

            // Check if agent wants to use tools
            if (isset($message['tool_calls']) && !empty($message['tool_calls'])) {
                $toolResults = $this->executeToolCalls($message['tool_calls']);

                // Add tool results to conversation and get final response
                $messages[] = $message;
                foreach ($toolResults as $toolResult) {
                    $messages[] = [
                        'role' => 'tool',
                        'tool_call_id' => $toolResult['tool_call_id'],
                        'content' => json_encode($toolResult['result'])
                    ];
                }

                // Get final response after tool execution
                $finalResponse = $this->callOpenAI(
                    $agent->getModel() ?? 'gpt-4',
                    $messages,
                    $tools,
                    $agent->getTemperature() ?? 0.7,
                    $agent->getMaxTokens() ?? 2000
                );

                $message = $finalResponse['choices'][0]['message'];
            }

            $agent->incrementSuccessCount();
            $this->entityManager->flush();

            return [
                'success' => true,
                'response' => $message['content'],
                'tool_calls' => $toolResults ?? [],
                'model' => $agent->getModel(),
                'usage' => $response['usage'] ?? null
            ];

        } catch (\Exception $e) {
            $agent->incrementFailureCount();
            $this->entityManager->flush();

            $this->logger->error('Agent execution failed', [
                'agent_id' => $agent->getId(),
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Prepare agent tools in OpenAI function calling format
     */
    private function prepareToolsForApi(IntelligentAgent $agent): array
    {
        $enabledTools = $agent->getTools() ?? [];
        if (empty($enabledTools)) {
            return [];
        }

        $availableTools = $this->toolsService->getAvailableTools();
        $tools = [];

        foreach ($enabledTools as $toolName) {
            if (!isset($availableTools[$toolName])) {
                continue;
            }

            $tool = $availableTools[$toolName];
            $tools[] = [
                'type' => 'function',
                'function' => [
                    'name' => $toolName,
                    'description' => $tool['description'],
                    'parameters' => [
                        'type' => 'object',
                        'properties' => $this->convertParametersToJsonSchema($tool['parameters']),
                        'required' => $this->getRequiredParameters($tool['parameters'])
                    ]
                ]
            ];
        }

        return $tools;
    }

    /**
     * Convert parameter definitions to JSON schema format
     */
    private function convertParametersToJsonSchema(array $parameters): array
    {
        $properties = [];
        foreach ($parameters as $name => $param) {
            $properties[$name] = [
                'type' => $param['type'],
                'description' => $param['description'] ?? ''
            ];
        }
        return $properties;
    }

    /**
     * Get list of required parameters
     */
    private function getRequiredParameters(array $parameters): array
    {
        $required = [];
        foreach ($parameters as $name => $param) {
            if ($param['required'] ?? false) {
                $required[] = $name;
            }
        }
        return $required;
    }

    /**
     * Execute tool calls requested by the agent
     */
    private function executeToolCalls(array $toolCalls): array
    {
        $results = [];

        foreach ($toolCalls as $toolCall) {
            $functionName = $toolCall['function']['name'];
            $arguments = json_decode($toolCall['function']['arguments'], true);

            $this->logger->info('Executing tool', [
                'tool' => $functionName,
                'arguments' => $arguments
            ]);

            $result = $this->toolsService->executeTool($functionName, $arguments);

            $results[] = [
                'tool_call_id' => $toolCall['id'],
                'tool_name' => $functionName,
                'arguments' => $arguments,
                'result' => $result
            ];
        }

        return $results;
    }

    /**
     * Call OpenAI API
     */
    private function callOpenAI(string $model, array $messages, array $tools, float $temperature, int $maxTokens): array
    {
        $payload = [
            'model' => $model,
            'messages' => $messages,
            'temperature' => $temperature,
            'max_tokens' => $maxTokens
        ];

        if (!empty($tools)) {
            $payload['tools'] = $tools;
            $payload['tool_choice'] = 'auto';
        }

        try {
            $response = $this->httpClient->request('POST', 'https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->openaiApiKey,
                    'Content-Type' => 'application/json'
                ],
                'json' => $payload
            ]);

            return $response->toArray();
        } catch (\Exception $e) {
            $this->logger->error('OpenAI API call failed', ['error' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }
}
