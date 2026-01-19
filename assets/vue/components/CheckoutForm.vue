<template>
  <div class="checkout-form">
    <!-- Progress Indicator -->
    <div class="flex items-center justify-between mb-8">
      <div
        v-for="(step, index) in steps"
        :key="index"
        class="flex items-center"
        :class="{ 'flex-1': index < steps.length - 1 }"
      >
        <div class="flex items-center">
          <div
            class="step-circle"
            :class="{
              'active': currentStep === index + 1,
              'completed': currentStep > index + 1
            }"
          >
            <span v-if="currentStep > index + 1">✓</span>
            <span v-else>{{ index + 1 }}</span>
          </div>
          <span class="hidden md:inline ml-2 text-sm font-medium">{{ step.name }}</span>
        </div>
        <div v-if="index < steps.length - 1" class="flex-1 h-1 bg-gray-200 mx-2"></div>
      </div>
    </div>

    <!-- Step 1: Customer Information -->
    <div v-show="currentStep === 1" class="form-step bg-white rounded-lg shadow-md p-6 mb-6">
      <h2 class="text-2xl font-bold mb-6 flex items-center">
        <span class="mr-3">👤</span>
        Customer Information
      </h2>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700 mb-2">Title</label>
          <select v-model="formData.title" class="form-input">
            <option value="">Select title</option>
            <option value="Mr">Mr</option>
            <option value="Mrs">Mrs</option>
            <option value="Ms">Ms</option>
            <option value="Miss">Miss</option>
            <option value="Dr">Dr</option>
            <option value="Prof">Prof</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            First Name <span class="text-red-500">*</span>
          </label>
          <input
            v-model="formData.firstName"
            type="text"
            required
            class="form-input"
            :class="{ 'border-red-500': errors.firstName }"
          />
          <p v-if="errors.firstName" class="text-red-500 text-sm mt-1">{{ errors.firstName }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Last Name <span class="text-red-500">*</span>
          </label>
          <input
            v-model="formData.lastName"
            type="text"
            required
            class="form-input"
            :class="{ 'border-red-500': errors.lastName }"
          />
          <p v-if="errors.lastName" class="text-red-500 text-sm mt-1">{{ errors.lastName }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Email <span class="text-red-500">*</span>
          </label>
          <input
            v-model="formData.email"
            type="email"
            required
            class="form-input"
            :class="{ 'border-red-500': errors.email }"
          />
          <p v-if="errors.email" class="text-red-500 text-sm mt-1">{{ errors.email }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Phone <span class="text-red-500">*</span>
          </label>
          <input
            v-model="formData.phone"
            type="tel"
            required
            class="form-input"
            :class="{ 'border-red-500': errors.phone }"
          />
          <p v-if="errors.phone" class="text-red-500 text-sm mt-1">{{ errors.phone }}</p>
        </div>
      </div>

      <!-- Create Account Option -->
      <div class="mt-6 pt-6 border-t border-gray-200">
        <label class="flex items-start cursor-pointer">
          <input
            v-model="formData.createAccount"
            type="checkbox"
            class="w-5 h-5 text-primary-600 border-gray-300 rounded focus:ring-primary-500 mt-1"
          />
          <div class="ml-3">
            <span class="text-lg font-semibold text-gray-900">Create an account</span>
            <p class="text-sm text-gray-600 mt-1">
              Save your information for faster checkout next time and track your orders
            </p>
          </div>
        </label>

        <div v-if="formData.createAccount" class="mt-4 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Password <span class="text-red-500">*</span>
            </label>
            <input
              v-model="formData.password"
              type="password"
              minlength="8"
              class="form-input"
              :class="{ 'border-red-500': errors.password }"
            />
            <p class="text-xs text-gray-500 mt-1">Minimum 8 characters</p>
            <p v-if="errors.password" class="text-red-500 text-sm mt-1">{{ errors.password }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Confirm Password <span class="text-red-500">*</span>
            </label>
            <input
              v-model="formData.passwordConfirm"
              type="password"
              minlength="8"
              class="form-input"
              :class="{ 'border-red-500': errors.passwordConfirm }"
            />
            <p v-if="errors.passwordConfirm" class="text-red-500 text-sm mt-1">{{ errors.passwordConfirm }}</p>
          </div>
        </div>
      </div>

      <button
        @click="nextStep"
        type="button"
        class="mt-6 btn btn-primary"
      >
        Continue to Address
        <span class="ml-2">→</span>
      </button>
    </div>

    <!-- Step 2: Address -->
    <div v-show="currentStep === 2" class="form-step bg-white rounded-lg shadow-md p-6 mb-6">
      <h2 class="text-2xl font-bold mb-6 flex items-center">
        <span class="mr-3">📍</span>
        Billing & Shipping Address
      </h2>

      <h3 class="text-lg font-bold mb-4">Billing Address</h3>
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Street Address <span class="text-red-500">*</span>
          </label>
          <input
            v-model="formData.billingAddress"
            type="text"
            required
            class="form-input"
            :class="{ 'border-red-500': errors.billingAddress }"
          />
          <p v-if="errors.billingAddress" class="text-red-500 text-sm mt-1">{{ errors.billingAddress }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Address Line 2 (Optional)</label>
          <input
            v-model="formData.billingAddressLine2"
            type="text"
            class="form-input"
            placeholder="Apartment, suite, unit, building, floor, etc."
          />
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Postal Code <span class="text-red-500">*</span>
            </label>
            <input
              v-model="formData.billingPostalCode"
              type="text"
              required
              class="form-input"
              :class="{ 'border-red-500': errors.billingPostalCode }"
            />
            <p v-if="errors.billingPostalCode" class="text-red-500 text-sm mt-1">{{ errors.billingPostalCode }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              City <span class="text-red-500">*</span>
            </label>
            <input
              v-model="formData.billingCity"
              type="text"
              required
              class="form-input"
              :class="{ 'border-red-500': errors.billingCity }"
            />
            <p v-if="errors.billingCity" class="text-red-500 text-sm mt-1">{{ errors.billingCity }}</p>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Country <span class="text-red-500">*</span>
          </label>
          <select
            v-model="formData.billingCountry"
            required
            class="form-input"
            :class="{ 'border-red-500': errors.billingCountry }"
          >
            <option value="">Select country</option>
            <option value="DE">Germany</option>
            <option value="AT">Austria</option>
            <option value="CH">Switzerland</option>
            <option value="FR">France</option>
            <option value="IT">Italy</option>
            <option value="ES">Spain</option>
            <option value="NL">Netherlands</option>
            <option value="BE">Belgium</option>
            <option value="PL">Poland</option>
            <option value="GB">United Kingdom</option>
          </select>
          <p v-if="errors.billingCountry" class="text-red-500 text-sm mt-1">{{ errors.billingCountry }}</p>
        </div>
      </div>

      <!-- Different Shipping Address -->
      <div class="mt-6 pt-6 border-t border-gray-200">
        <label class="flex items-center cursor-pointer">
          <input
            v-model="formData.differentShipping"
            type="checkbox"
            class="w-5 h-5 text-primary-600 border-gray-300 rounded focus:ring-primary-500"
          />
          <span class="ml-3 text-lg font-semibold text-gray-900">Ship to a different address</span>
        </label>

        <div v-if="formData.differentShipping" class="mt-6 space-y-4">
          <h3 class="text-lg font-bold mb-4">Shipping Address</h3>
          <!-- Shipping address fields (similar to billing) -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Street Address *</label>
            <input v-model="formData.shippingAddress" type="text" class="form-input" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Address Line 2 (Optional)</label>
            <input v-model="formData.shippingAddressLine2" type="text" class="form-input" />
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Postal Code *</label>
              <input v-model="formData.shippingPostalCode" type="text" class="form-input" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">City *</label>
              <input v-model="formData.shippingCity" type="text" class="form-input" />
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Country *</label>
            <select v-model="formData.shippingCountry" class="form-input">
              <option value="">Select country</option>
              <option value="DE">Germany</option>
              <option value="AT">Austria</option>
              <option value="CH">Switzerland</option>
            </select>
          </div>
        </div>
      </div>

      <div class="flex gap-4 mt-6">
        <button @click="previousStep" type="button" class="btn btn-secondary">
          ← Back
        </button>
        <button @click="nextStep" type="button" class="btn btn-primary">
          Continue to Shipping →
        </button>
      </div>
    </div>

    <!-- Step 3: Shipping & Payment -->
    <div v-show="currentStep === 3" class="form-step bg-white rounded-lg shadow-md p-6 mb-6">
      <h2 class="text-2xl font-bold mb-6 flex items-center">
        <span class="mr-3">🚚</span>
        Shipping & Payment
      </h2>

      <h3 class="text-lg font-bold mb-4">Shipping Method</h3>
      <div class="space-y-3 mb-6">
        <label
          v-for="method in shippingMethods"
          :key="method.id"
          class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50"
          :class="{ 'border-primary-500 bg-primary-50': formData.shippingMethodId === method.id }"
        >
          <input
            v-model="formData.shippingMethodId"
            type="radio"
            :value="method.id"
            class="w-4 h-4 text-primary-600"
          />
          <div class="ml-3 flex-1">
            <div class="font-semibold">{{ method.name }}</div>
            <div class="text-sm text-gray-600">{{ method.description }}</div>
          </div>
          <div class="font-bold">{{ formatPrice(method.price) }}</div>
        </label>
      </div>

      <h3 class="text-lg font-bold mb-4">Payment Method</h3>
      <div class="space-y-3 mb-6">
        <label
          v-for="method in paymentMethods"
          :key="method.id"
          class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50"
          :class="{ 'border-primary-500 bg-primary-50': formData.paymentMethodId === method.id }"
        >
          <input
            v-model="formData.paymentMethodId"
            type="radio"
            :value="method.id"
            class="w-4 h-4 text-primary-600"
          />
          <div class="ml-3 flex-1">
            <div class="font-semibold">{{ method.name }}</div>
            <div class="text-sm text-gray-600">{{ method.description }}</div>
          </div>
          <div v-if="method.fee > 0" class="font-bold">+{{ formatPrice(method.fee) }}</div>
        </label>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Order Notes (Optional)</label>
        <textarea
          v-model="formData.notes"
          class="form-input"
          rows="3"
          placeholder="Special instructions or notes about your order..."
        ></textarea>
      </div>

      <div class="flex gap-4 mt-6">
        <button @click="previousStep" type="button" class="btn btn-secondary">
          ← Back
        </button>
        <button @click="submitOrder" type="button" class="btn btn-primary" :disabled="submitting">
          <span v-if="submitting">Processing...</span>
          <span v-else>Place Order →</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';

const props = defineProps({
  csrfToken: {
    type: String,
    required: true
  },
  shippingMethods: {
    type: Array,
    default: () => []
  },
  paymentMethods: {
    type: Array,
    default: () => []
  }
});

const currentStep = ref(1);
const submitting = ref(false);

const steps = [
  { name: 'Customer Info', number: 1 },
  { name: 'Address', number: 2 },
  { name: 'Shipping & Payment', number: 3 }
];

const formData = reactive({
  title: '',
  firstName: '',
  lastName: '',
  email: '',
  phone: '',
  createAccount: false,
  password: '',
  passwordConfirm: '',
  billingAddress: '',
  billingAddressLine2: '',
  billingCity: '',
  billingPostalCode: '',
  billingCountry: 'DE',
  differentShipping: false,
  shippingAddress: '',
  shippingAddressLine2: '',
  shippingCity: '',
  shippingPostalCode: '',
  shippingCountry: '',
  shippingMethodId: null,
  paymentMethodId: null,
  notes: ''
});

const errors = reactive({});

const formatPrice = (price) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'EUR'
  }).format(price);
};

const validateStep1 = () => {
  const newErrors = {};

  if (!formData.firstName.trim()) {
    newErrors.firstName = 'First name is required';
  }
  if (!formData.lastName.trim()) {
    newErrors.lastName = 'Last name is required';
  }
  if (!formData.email.trim()) {
    newErrors.email = 'Email is required';
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) {
    newErrors.email = 'Invalid email format';
  }
  if (!formData.phone.trim()) {
    newErrors.phone = 'Phone is required';
  }

  if (formData.createAccount) {
    if (!formData.password) {
      newErrors.password = 'Password is required';
    } else if (formData.password.length < 8) {
      newErrors.password = 'Password must be at least 8 characters';
    }
    if (formData.password !== formData.passwordConfirm) {
      newErrors.passwordConfirm = 'Passwords do not match';
    }
  }

  Object.assign(errors, newErrors);
  return Object.keys(newErrors).length === 0;
};

const validateStep2 = () => {
  const newErrors = {};

  if (!formData.billingAddress.trim()) {
    newErrors.billingAddress = 'Billing address is required';
  }
  if (!formData.billingCity.trim()) {
    newErrors.billingCity = 'City is required';
  }
  if (!formData.billingPostalCode.trim()) {
    newErrors.billingPostalCode = 'Postal code is required';
  }
  if (!formData.billingCountry) {
    newErrors.billingCountry = 'Country is required';
  }

  Object.assign(errors, newErrors);
  return Object.keys(newErrors).length === 0;
};

const nextStep = () => {
  // Clear previous errors
  Object.keys(errors).forEach(key => delete errors[key]);

  if (currentStep.value === 1 && !validateStep1()) {
    return;
  }
  if (currentStep.value === 2 && !validateStep2()) {
    return;
  }

  if (currentStep.value < 3) {
    currentStep.value++;
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
};

const previousStep = () => {
  if (currentStep.value > 1) {
    currentStep.value--;
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
};

const submitOrder = async () => {
  if (!formData.shippingMethodId || !formData.paymentMethodId) {
    alert('Please select both shipping and payment methods');
    return;
  }

  submitting.value = true;

  try {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/checkout/process';

    // Add all form data
    const data = {
      '_csrf_token': props.csrfToken,
      'title': formData.title,
      'first_name': formData.firstName,
      'last_name': formData.lastName,
      'email': formData.email,
      'phone': formData.phone,
      'billing_address': formData.billingAddress,
      'billing_address_line2': formData.billingAddressLine2,
      'billing_city': formData.billingCity,
      'billing_postal_code': formData.billingPostalCode,
      'billing_country': formData.billingCountry,
      'different_shipping': formData.differentShipping ? '1' : '',
      'shipping_address': formData.differentShipping ? formData.shippingAddress : formData.billingAddress,
      'shipping_address_line2': formData.differentShipping ? formData.shippingAddressLine2 : formData.billingAddressLine2,
      'shipping_city': formData.differentShipping ? formData.shippingCity : formData.billingCity,
      'shipping_postal_code': formData.differentShipping ? formData.shippingPostalCode : formData.billingPostalCode,
      'shipping_country': formData.differentShipping ? formData.shippingCountry : formData.billingCountry,
      'shipping_method_id': formData.shippingMethodId,
      'payment_method_id': formData.paymentMethodId,
      'notes': formData.notes,
      'create_account': formData.createAccount ? '1' : '',
      'password': formData.password,
      'password_confirm': formData.passwordConfirm
    };

    Object.keys(data).forEach(key => {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = key;
      input.value = data[key];
      form.appendChild(input);
    });

    document.body.appendChild(form);
    form.submit();
  } catch (error) {
    console.error('Error submitting order:', error);
    alert('An error occurred. Please try again.');
    submitting.value = false;
  }
};
</script>

<style scoped>
.step-circle {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-center;
  font-weight: bold;
  background: #e5e7eb;
  color: #6b7280;
  transition: all 0.3s;
}

.step-circle.active {
  background: #667eea;
  color: white;
  transform: scale(1.1);
}

.step-circle.completed {
  background: #10b981;
  color: white;
}
</style>
