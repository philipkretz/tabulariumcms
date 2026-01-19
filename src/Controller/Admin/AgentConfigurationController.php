<?php

namespace App\Controller\Admin;

use App\Entity\IntelligentAgent;
use App\Repository\IntelligentAgentRepository;
use App\Service\AgentToolsService;
use App\Service\AgentExecutorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/agent-configuration')]
#[IsGranted('ROLE_ADMIN')]
class AgentConfigurationController extends AbstractController
{
    public function __construct(
        private IntelligentAgentRepository $agentRepository,
        private EntityManagerInterface $entityManager,
        private AgentToolsService $toolsService,
        private AgentExecutorService $executorService
    ) {
    }

    #[Route('', name: 'admin_agent_configuration')]
    public function index(): Response
    {
        $agents = $this->agentRepository->findAll();
        $statistics = $this->agentRepository->getStatistics();

        return $this->render('admin/agent_configuration/index.html.twig', [
            'agents' => $agents,
            'statistics' => $statistics
        ]);
    }

    #[Route('/create', name: 'admin_agent_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $agent = new IntelligentAgent();
        $agent->setName($data['name'] ?? 'New Agent');
        $agent->setSlug($data['slug'] ?? 'agent-' . uniqid());
        $agent->setDescription($data['description'] ?? '');
        $agent->setType($data['type'] ?? IntelligentAgent::TYPE_CUSTOM);
        $agent->setSystemPrompt($data['systemPrompt'] ?? 'You are a helpful AI assistant.');
        $agent->setIsActive($data['isActive'] ?? false);

        $this->entityManager->persist($agent);
        $this->entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Agent created successfully',
            'agent' => [
                'id' => $agent->getId(),
                'name' => $agent->getName(),
                'slug' => $agent->getSlug()
            ]
        ]);
    }

    #[Route('/tools', name: 'admin_agent_tools', methods: ['GET'])]
    public function getTools(): JsonResponse
    {
        $tools = $this->toolsService->getAvailableTools();

        // Group tools by category
        $grouped = [];
        foreach ($tools as $key => $tool) {
            $category = $tool['category'];
            if (!isset($grouped[$category])) {
                $grouped[$category] = [];
            }
            $grouped[$category][$key] = $tool;
        }

        return new JsonResponse([
            'success' => true,
            'tools' => $tools,
            'grouped' => $grouped
        ]);
    }

    #[Route('/{id}', name: 'admin_agent_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $agent = $this->agentRepository->find($id);

        if (!$agent) {
            return new JsonResponse(['error' => 'Agent not found'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse([
            'id' => $agent->getId(),
            'name' => $agent->getName(),
            'slug' => $agent->getSlug(),
            'description' => $agent->getDescription(),
            'type' => $agent->getType(),
            'systemPrompt' => $agent->getSystemPrompt(),
            'configuration' => $agent->getConfiguration(),
            'tools' => $agent->getTools(),
            'workflow' => $agent->getWorkflow(),
            'model' => $agent->getModel(),
            'temperature' => $agent->getTemperature(),
            'maxTokens' => $agent->getMaxTokens(),
            'isActive' => $agent->isActive(),
            'priority' => $agent->getPriority(),
            'executionCount' => $agent->getExecutionCount(),
            'successRate' => $agent->getSuccessRate()
        ]);
    }

    #[Route('/{id}/update', name: 'admin_agent_update', methods: ['POST', 'PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $agent = $this->agentRepository->find($id);

        if (!$agent) {
            return new JsonResponse(['error' => 'Agent not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['name'])) $agent->setName($data['name']);
        if (isset($data['description'])) $agent->setDescription($data['description']);
        if (isset($data['type'])) $agent->setType($data['type']);
        if (isset($data['systemPrompt'])) $agent->setSystemPrompt($data['systemPrompt']);
        if (isset($data['configuration'])) $agent->setConfiguration($data['configuration']);
        if (isset($data['tools'])) $agent->setTools($data['tools']);
        if (isset($data['workflow'])) $agent->setWorkflow($data['workflow']);
        if (isset($data['model'])) $agent->setModel($data['model']);
        if (isset($data['temperature'])) $agent->setTemperature($data['temperature']);
        if (isset($data['maxTokens'])) $agent->setMaxTokens($data['maxTokens']);
        if (isset($data['isActive'])) $agent->setIsActive($data['isActive']);
        if (isset($data['priority'])) $agent->setPriority($data['priority']);
        if (isset($data['triggerEvent'])) $agent->setTriggerEvent($data['triggerEvent']);
        if (isset($data['triggerConditions'])) $agent->setTriggerConditions($data['triggerConditions']);

        $this->entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Agent updated successfully'
        ]);
    }

    #[Route('/{id}/toggle', name: 'admin_agent_toggle', methods: ['POST'])]
    public function toggle(int $id): JsonResponse
    {
        $agent = $this->agentRepository->find($id);

        if (!$agent) {
            return new JsonResponse(['error' => 'Agent not found'], Response::HTTP_NOT_FOUND);
        }

        $agent->setIsActive(!$agent->isActive());
        $this->entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => $agent->isActive() ? 'Agent activated' : 'Agent deactivated',
            'isActive' => $agent->isActive()
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_agent_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $agent = $this->agentRepository->find($id);

        if (!$agent) {
            return new JsonResponse(['error' => 'Agent not found'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($agent);
        $this->entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Agent deleted successfully'
        ]);
    }

    #[Route('/{id}/test', name: 'admin_agent_test', methods: ['POST'])]
    public function test(int $id, Request $request): JsonResponse
    {
        $agent = $this->agentRepository->find($id);

        if (!$agent) {
            return new JsonResponse(['error' => 'Agent not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        $input = $data['input'] ?? 'Hello, test the agent!';

        // Execute the agent with the executor service
        $result = $this->executorService->execute($agent, $input);

        return new JsonResponse($result);
    }
}

