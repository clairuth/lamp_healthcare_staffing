import React, { useState, useEffect } from 'react';
import { 
  Box, 
  VStack, 
  HStack, 
  Heading, 
  Text, 
  Button, 
  Card,
  CardHeader,
  CardBody,
  CardFooter,
  Divider,
  useToast,
  useColorModeValue,
  Icon,
  Flex,
  Badge,
  Table,
  Thead,
  Tbody,
  Tr,
  Th,
  Td,
  Modal,
  ModalOverlay,
  ModalContent,
  ModalHeader,
  ModalFooter,
  ModalBody,
  ModalCloseButton,
  useDisclosure,
  Spinner,
  FormControl,
  FormLabel,
  Input,
  Textarea,
  Select,
  Image,
  Center
} from '@chakra-ui/react';
import { useNavigate } from 'react-router-dom';
import { FaMoneyBillWave, FaPaypal, FaBitcoin, FaMobileAlt, FaExclamationTriangle, FaCheckCircle } from 'react-icons/fa';

const PaymentIntegrationSettings = () => {
  const navigate = useNavigate();
  const toast = useToast();
  const { isOpen, onOpen, onClose } = useDisclosure();
  const [isLoading, setIsLoading] = useState(true);
  const [isSaving, setIsSaving] = useState(false);
  const [activeTab, setActiveTab] = useState('paypal');
  
  const cardBg = useColorModeValue('white', 'gray.700');
  const borderColor = useColorModeValue('gray.200', 'gray.600');
  
  const [integrationSettings, setIntegrationSettings] = useState({
    paypal: {
      enabled: true,
      business_email: 'cubeloid@gmail.com',
      client_id: 'AaBbCcDdEeFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtUu',
      client_secret: '••••••••••••••••••••••••••••••••••••••••••',
      sandbox_mode: true,
      webhook_url: 'https://yourdomain.com/api/webhooks/paypal',
      escrow_period_days: 3,
      transaction_fee_percent: 2.9,
      fixed_fee: 0.30
    },
    cashapp: {
      enabled: true,
      cashtag: 'clairuth',
      api_key: 'CcDdEeFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtUuVv',
      api_secret: '••••••••••••••••••••••••••••••••••••••••••',
      webhook_url: 'https://yourdomain.com/api/webhooks/cashapp',
      escrow_period_days: 3,
      transaction_fee_percent: 0,
      fixed_fee: 0
    },
    coinbase: {
      enabled: true,
      api_key: 'EeFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtUuVvWwXx',
      api_secret: '••••••••••••••••••••••••••••••••••••••••••',
      account_email: 'cubeloid@gmail.com',
      webhook_url: 'https://yourdomain.com/api/webhooks/coinbase',
      escrow_period_days: 3,
      transaction_fee_percent: 1.0,
      fixed_fee: 0
    },
    zelle: {
      enabled: true,
      email: 'cubeloid@gmail.com',
      phone: '+1234567890',
      webhook_url: 'https://yourdomain.com/api/webhooks/zelle',
      escrow_period_days: 3,
      transaction_fee_percent: 0,
      fixed_fee: 0
    },
    escrow: {
      default_period_days: 3,
      auto_release: true,
      dispute_window_hours: 72,
      admin_email_notifications: true,
      user_email_notifications: true
    }
  });

  useEffect(() => {
    // In a real implementation, this would be an API call to fetch integration settings
    setTimeout(() => {
      setIsLoading(false);
    }, 1000);
  }, []);

  const handleTabChange = (tab) => {
    setActiveTab(tab);
  };

  const handleToggleEnable = (provider) => {
    setIntegrationSettings(prev => ({
      ...prev,
      [provider]: {
        ...prev[provider],
        enabled: !prev[provider].enabled
      }
    }));
  };

  const handleInputChange = (provider, field, value) => {
    setIntegrationSettings(prev => ({
      ...prev,
      [provider]: {
        ...prev[provider],
        [field]: value
      }
    }));
  };

  const handleSaveSettings = () => {
    setIsSaving(true);
    
    // In a real implementation, this would be an API call to save the settings
    setTimeout(() => {
      toast({
        title: 'Settings Saved',
        description: 'Your payment integration settings have been saved successfully.',
        status: 'success',
        duration: 5000,
        isClosable: true,
      });
      setIsSaving(false);
    }, 1500);
  };

  const handleTestConnection = (provider) => {
    setIsSaving(true);
    
    // In a real implementation, this would be an API call to test the connection
    setTimeout(() => {
      if (integrationSettings[provider].enabled) {
        toast({
          title: 'Connection Successful',
          description: `Successfully connected to ${getProviderName(provider)}.`,
          status: 'success',
          duration: 5000,
          isClosable: true,
        });
      } else {
        toast({
          title: 'Connection Failed',
          description: `${getProviderName(provider)} integration is disabled. Please enable it first.`,
          status: 'error',
          duration: 5000,
          isClosable: true,
        });
      }
      setIsSaving(false);
    }, 1500);
  };

  const getProviderName = (provider) => {
    switch (provider) {
      case 'paypal':
        return 'PayPal';
      case 'cashapp':
        return 'Cash App';
      case 'coinbase':
        return 'Coinbase';
      case 'zelle':
        return 'Zelle';
      case 'escrow':
        return 'Escrow System';
      default:
        return provider;
    }
  };

  const getProviderIcon = (provider) => {
    switch (provider) {
      case 'paypal':
        return FaPaypal;
      case 'cashapp':
        return FaMobileAlt;
      case 'coinbase':
        return FaBitcoin;
      case 'zelle':
        return FaMobileAlt;
      case 'escrow':
        return FaMoneyBillWave;
      default:
        return FaMoneyBillWave;
    }
  };

  const getProviderColor = (provider) => {
    switch (provider) {
      case 'paypal':
        return 'blue';
      case 'cashapp':
        return 'green';
      case 'coinbase':
        return 'orange';
      case 'zelle':
        return 'purple';
      case 'escrow':
        return 'teal';
      default:
        return 'gray';
    }
  };

  const renderPayPalSettings = () => (
    <VStack spacing={6} align="stretch">
      <Flex justify="space-between" align="center">
        <HStack>
          <Icon as={FaPaypal} color="blue.500" boxSize={6} />
          <Heading size="md">PayPal Integration</Heading>
        </HStack>
        <Button
          size="sm"
          colorScheme={integrationSettings.paypal.enabled ? "green" : "gray"}
          onClick={() => handleToggleEnable('paypal')}
        >
          {integrationSettings.paypal.enabled ? "Enabled" : "Disabled"}
        </Button>
      </Flex>
      
      <Divider />
      
      <FormControl>
        <FormLabel>Business Email</FormLabel>
        <Input
          value={integrationSettings.paypal.business_email}
          onChange={(e) => handleInputChange('paypal', 'business_email', e.target.value)}
          isDisabled={!integrationSettings.paypal.enabled}
        />
      </FormControl>
      
      <FormControl>
        <FormLabel>Client ID</FormLabel>
        <Input
          value={integrationSettings.paypal.client_id}
          onChange={(e) => handleInputChange('paypal', 'client_id', e.target.value)}
          isDisabled={!integrationSettings.paypal.enabled}
        />
      </FormControl>
      
      <FormControl>
        <FormLabel>Client Secret</FormLabel>
        <Input
          type="password"
          value={integrationSettings.paypal.client_secret}
          onChange={(e) => handleInputChange('paypal', 'client_secret', e.target.value)}
          isDisabled={!integrationSettings.paypal.enabled}
        />
      </FormControl>
      
      <FormControl>
        <FormLabel>Webhook URL</FormLabel>
        <Input
          value={integrationSettings.paypal.webhook_url}
          onChange={(e) => handleInputChange('paypal', 'webhook_url', e.target.value)}
          isDisabled={!integrationSettings.paypal.enabled}
        />
      </FormControl>
      
      <HStack spacing={4}>
        <FormControl>
          <FormLabel>Escrow Period (Days)</FormLabel>
          <Input
            type="number"
            value={integrationSettings.paypal.escrow_period_days}
            onChange={(e) => handleInputChange('paypal', 'escrow_period_days', parseInt(e.target.value))}
            isDisabled={!integrationSettings.paypal.enabled}
          />
        </FormControl>
        
        <FormControl>
          <FormLabel>Transaction Fee (%)</FormLabel>
          <Input
            type="number"
            step="0.1"
            value={integrationSettings.paypal.transaction_fee_percent}
            onChange={(e) => handleInputChange('paypal', 'transaction_fee_percent', parseFloat(e.target.value))}
            isDisabled={!integrationSettings.paypal.enabled}
          />
        </FormControl>
        
        <FormControl>
          <FormLabel>Fixed Fee ($)</FormLabel>
          <Input
            type="number"
            step="0.01"
            value={integrationSettings.paypal.fixed_fee}
            onChange={(e) => handleInputChange('paypal', 'fixed_fee', parseFloat(e.target.value))}
            isDisabled={!integrationSettings.paypal.enabled}
          />
        </FormControl>
      </HStack>
      
      <FormControl>
        <label style={{ display: 'flex', alignItems: 'center' }}>
          <input
            type="checkbox"
            checked={integrationSettings.paypal.sandbox_mode}
            onChange={(e) => handleInputChange('paypal', 'sandbox_mode', e.target.checked)}
            disabled={!integrationSettings.paypal.enabled}
            style={{ marginRight: '8px' }}
          />
          Enable Sandbox Mode (Test Environment)
        </label>
      </FormControl>
      
      <HStack spacing={4}>
        <Button
          colorScheme="blue"
          onClick={() => handleTestConnection('paypal')}
          isLoading={isSaving}
          isDisabled={!integrationSettings.paypal.enabled}
        >
          Test Connection
        </Button>
      </HStack>
    </VStack>
  );

  const renderCashAppSettings = () => (
    <VStack spacing={6} align="stretch">
      <Flex justify="space-between" align="center">
        <HStack>
          <Icon as={FaMobileAlt} color="green.500" boxSize={6} />
          <Heading size="md">Cash App Integration</Heading>
        </HStack>
        <Button
          size="sm"
          colorScheme={integrationSettings.cashapp.enabled ? "green" : "gray"}
          onClick={() => handleToggleEnable('cashapp')}
        >
          {integrationSettings.cashapp.enabled ? "Enabled" : "Disabled"}
        </Button>
      </Flex>
      
      <Divider />
      
      <FormControl>
        <FormLabel>$Cashtag</FormLabel>
        <Input
          value={integrationSettings.cashapp.cashtag}
          onChange={(e) => handleInputChange('cashapp', 'cashtag', e.target.value)}
          isDisabled={!integrationSettings.cashapp.enabled}
        />
      </FormControl>
      
      <FormControl>
        <FormLabel>API Key</FormLabel>
        <Input
          value={integrationSettings.cashapp.api_key}
          onChange={(e) => handleInputChange('cashapp', 'api_key', e.target.value)}
          isDisabled={!integrationSettings.cashapp.enabled}
        />
      </FormControl>
      
      <FormControl>
        <FormLabel>API Secret</FormLabel>
        <Input
          type="password"
          value={integrationSettings.cashapp.api_secret}
          onChange={(e) => handleInputChange('cashapp', 'api_secret', e.target.value)}
          isDisabled={!integrationSettings.cashapp.enabled}
        />
      </FormControl>
      
      <FormControl>
        <FormLabel>Webhook URL</FormLabel>
        <Input
          value={integrationSettings.cashapp.webhook_url}
          onChange={(e) => handleInputChange('cashapp', 'webhook_url', e.target.value)}
          isDisabled={!integrationSettings.cashapp.enabled}
        />
      </FormControl>
      
      <HStack spacing={4}>
        <FormControl>
          <FormLabel>Escrow Period (Days)</FormLabel>
          <Input
            type="number"
            value={integrationSettings.cashapp.escrow_period_days}
            onChange={(e) => handleInputChange('cashapp', 'escrow_period_days', parseInt(e.target.value))}
            isDisabled={!integrationSettings.cashapp.enabled}
          />
        </FormControl>
        
        <FormControl>
          <FormLabel>Transaction Fee (%)</FormLabel>
          <Input
            type="number"
            step="0.1"
            value={integrationSettings.cashapp.transaction_fee_percent}
            onChange={(e) => handleInputChange('cashapp', 'transaction_fee_percent', parseFloat(e.target.value))}
            isDisabled={!integrationSettings.cashapp.enabled}
          />
        </FormControl>
        
        <FormControl>
          <FormLabel>Fixed Fee ($)</FormLabel>
          <Input
            type="number"
            step="0.01"
            value={integrationSettings.cashapp.fixed_fee}
            onChange={(e) => handleInputChange('cashapp', 'fixed_fee', parseFloat(e.target.value))}
            isDisabled={!integrationSettings.cashapp.enabled}
          />
        </FormControl>
      </HStack>
      
      <HStack spacing={4}>
        <Button
          colorScheme="green"
          onClick={() => handleTestConnection('cashapp')}
          isLoading={isSaving}
          isDisabled={!integrationSettings.cashapp.enabled}
        >
          Test Connection
        </Button>
      </HStack>
    </VStack>
  );

  const renderCoinbaseSettings = () => (
    <VStack spacing={6} align="stretch">
      <Flex justify="space-between" align="center">
        <HStack>
          <Icon as={FaBitcoin} color="orange.500" boxSize={6} />
          <Heading size="md">Coinbase Integration</Heading>
        </HStack>
        <Button
          size="sm"
          colorScheme={integrationSettings.coinbase.enabled ? "green" : "gray"}
          onClick={() => handleToggleEnable('coinbase')}
        >
          {integrationSettings.coinbase.enabled ? "Enabled" : "Disabled"}
        </Button>
      </Flex>
      
      <Divider />
      
      <FormControl>
        <FormLabel>Account Email</FormLabel>
        <Input
          value={integrationSettings.coinbase.account_email}
          onChange={(e) => handleInputChange('coinbase', 'account_email', e.target.value)}
          isDisabled={!integrationSettings.coinbase.enabled}
        />
      </FormControl>
      
      <FormControl>
        <FormLabel>API Key</FormLabel>
        <Input
          value={integrationSettings.coinbase.api_key}
          onChange={(e) => handleInputChange('coinbase', 'api_key', e.target.value)}
          isDisabled={!integrationSettings.coinbase.enabled}
        />
      </FormControl>
      
      <FormControl>
        <FormLabel>API Secret</FormLabel>
        <Input
          type="password"
          value={integrationSettings.coinbase.api_secret}
          onChange={(e) => handleInputChange('coinbase', 'api_secret', e.target.value)}
          isDisabled={!integrationSettings.coinbase.enabled}
        />
      </FormControl>
      
      <FormControl>
        <FormLabel>Webhook URL</FormLabel>
        <Input
          value={integrationSettings.coinbase.webhook_url}
          onChange={(e) => handleInputChange('coinbase', 'webhook_url', e.target.value)}
          isDisabled={!integrationSettings.coinbase.enabled}
        />
      </FormControl>
      
      <HStack spacing={4}>
        <FormControl>
          <FormLabel>Escrow Period (Days)</FormLabel>
          <Input
            type="number"
            value={integrationSettings.coinbase.escrow_period_days}
            onChange={(e) => handleInputChange('coinbase', 'escrow_period_days', parseInt(e.target.value))}
            isDisabled={!integrationSettings.coinbase.enabled}
          />
        </FormControl>
        
        <FormControl>
          <FormLabel>Transaction Fee (%)</FormLabel>
          <Input
            type="number"
            step="0.1"
            value={integrationSettings.coinbase.transaction_fee_percent}
            onChange={(e) => handleInputChange('coinbase', 'transaction_fee_percent', parseFloat(e.target.value))}
            isDisabled={!integrationSettings.coinbase.enabled}
          />
        </FormControl>
        
        <FormC
(Content truncated due to size limit. Use line ranges to read in chunks)