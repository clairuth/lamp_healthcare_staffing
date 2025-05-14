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
  Tabs,
  TabList,
  TabPanels,
  Tab,
  TabPanel,
  FormControl,
  FormLabel,
  Input,
  Select,
  Textarea,
  Modal,
  ModalOverlay,
  ModalContent,
  ModalHeader,
  ModalFooter,
  ModalBody,
  ModalCloseButton,
  useDisclosure
} from '@chakra-ui/react';
import { useNavigate } from 'react-router-dom';
import { FaMoneyBillWave, FaCreditCard, FaPaypal, FaBitcoin, FaMobileAlt } from 'react-icons/fa';

const PaymentMethods = () => {
  const navigate = useNavigate();
  const toast = useToast();
  const { isOpen, onOpen, onClose } = useDisclosure();
  const [isLoading, setIsLoading] = useState(true);
  const [paymentMethods, setPaymentMethods] = useState([]);
  const [activeTab, setActiveTab] = useState(0);
  
  const cardBg = useColorModeValue('white', 'gray.700');
  const borderColor = useColorModeValue('gray.200', 'gray.600');
  
  const [formData, setFormData] = useState({
    method_type: 'paypal',
    account_identifier: '',
    nickname: '',
    is_default: false
  });

  useEffect(() => {
    // In a real implementation, this would be an API call
    // Simulate fetching payment methods
    setTimeout(() => {
      setPaymentMethods([
        {
          id: '1',
          method_type: 'paypal',
          account_identifier: '@cubeloid',
          nickname: 'My PayPal',
          is_default: true,
          status: 'active',
          created_at: new Date(2025, 2, 15)
        },
        {
          id: '2',
          method_type: 'cashapp',
          account_identifier: '@clairuth',
          nickname: 'CashApp Account',
          is_default: false,
          status: 'active',
          created_at: new Date(2025, 3, 5)
        },
        {
          id: '3',
          method_type: 'coinbase',
          account_identifier: 'cubeloid@gmail.com',
          nickname: 'Coinbase',
          is_default: false,
          status: 'active',
          created_at: new Date(2025, 3, 10)
        }
      ]);
      setIsLoading(false);
    }, 1000);
  }, []);

  const handleChange = (e) => {
    const { name, value, type, checked } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: type === 'checkbox' ? checked : value
    }));
  };

  const handleSubmit = () => {
    setIsLoading(true);
    
    // In a real implementation, this would be an API call to add the payment method
    setTimeout(() => {
      // Generate a new ID
      const newId = (paymentMethods.length + 1).toString();
      
      // Create new payment method
      const newPaymentMethod = {
        id: newId,
        method_type: formData.method_type,
        account_identifier: formData.account_identifier,
        nickname: formData.nickname || getDefaultNickname(formData.method_type),
        is_default: formData.is_default,
        status: 'active',
        created_at: new Date()
      };
      
      // If this is the default, update other methods
      let updatedMethods = [...paymentMethods];
      if (formData.is_default) {
        updatedMethods = updatedMethods.map(method => ({
          ...method,
          is_default: false
        }));
      }
      
      // Add the new method
      setPaymentMethods([...updatedMethods, newPaymentMethod]);
      
      toast({
        title: 'Payment Method Added',
        description: 'Your payment method has been successfully added.',
        status: 'success',
        duration: 5000,
        isClosable: true,
      });
      
      // Reset form and close modal
      setFormData({
        method_type: 'paypal',
        account_identifier: '',
        nickname: '',
        is_default: false
      });
      onClose();
      setIsLoading(false);
    }, 1500);
  };

  const handleDelete = (id) => {
    setIsLoading(true);
    
    // In a real implementation, this would be an API call to delete the payment method
    setTimeout(() => {
      // Remove the payment method
      const updatedMethods = paymentMethods.filter(method => method.id !== id);
      
      // If the deleted method was the default, set a new default
      if (paymentMethods.find(method => method.id === id)?.is_default && updatedMethods.length > 0) {
        updatedMethods[0].is_default = true;
      }
      
      setPaymentMethods(updatedMethods);
      
      toast({
        title: 'Payment Method Deleted',
        description: 'Your payment method has been successfully deleted.',
        status: 'success',
        duration: 5000,
        isClosable: true,
      });
      
      setIsLoading(false);
    }, 1000);
  };

  const handleSetDefault = (id) => {
    setIsLoading(true);
    
    // In a real implementation, this would be an API call to update the payment method
    setTimeout(() => {
      // Update all methods
      const updatedMethods = paymentMethods.map(method => ({
        ...method,
        is_default: method.id === id
      }));
      
      setPaymentMethods(updatedMethods);
      
      toast({
        title: 'Default Payment Method Updated',
        description: 'Your default payment method has been updated.',
        status: 'success',
        duration: 5000,
        isClosable: true,
      });
      
      setIsLoading(false);
    }, 1000);
  };

  const getMethodIcon = (methodType) => {
    switch (methodType) {
      case 'paypal':
        return FaPaypal;
      case 'cashapp':
        return FaMobileAlt;
      case 'coinbase':
        return FaBitcoin;
      case 'zelle':
        return FaMobileAlt;
      case 'bank_account':
        return FaMoneyBillWave;
      case 'credit_card':
        return FaCreditCard;
      default:
        return FaMoneyBillWave;
    }
  };

  const getMethodColor = (methodType) => {
    switch (methodType) {
      case 'paypal':
        return 'blue';
      case 'cashapp':
        return 'green';
      case 'coinbase':
        return 'orange';
      case 'zelle':
        return 'purple';
      case 'bank_account':
        return 'teal';
      case 'credit_card':
        return 'red';
      default:
        return 'gray';
    }
  };

  const getMethodName = (methodType) => {
    switch (methodType) {
      case 'paypal':
        return 'PayPal';
      case 'cashapp':
        return 'Cash App';
      case 'coinbase':
        return 'Coinbase';
      case 'zelle':
        return 'Zelle';
      case 'bank_account':
        return 'Bank Account';
      case 'credit_card':
        return 'Credit Card';
      default:
        return 'Other';
    }
  };

  const getDefaultNickname = (methodType) => {
    return `My ${getMethodName(methodType)}`;
  };

  return (
    <Box p={4}>
      <VStack spacing={8} align="stretch">
        <Flex 
          direction={{ base: 'column', md: 'row' }} 
          justify="space-between" 
          align={{ base: 'flex-start', md: 'center' }}
        >
          <Box>
            <Heading size="lg">Payment Methods</Heading>
            <Text color="gray.600">Manage your payment methods for receiving payments</Text>
          </Box>
          
          <Button 
            colorScheme="blue" 
            onClick={onOpen}
            leftIcon={<Icon as={FaMoneyBillWave} />}
            mt={{ base: 4, md: 0 }}
          >
            Add Payment Method
          </Button>
        </Flex>
        
        <Card bg={cardBg} borderWidth="1px" borderColor={borderColor} borderRadius="lg" shadow="md">
          <CardHeader>
            <Tabs variant="enclosed" index={activeTab} onChange={setActiveTab}>
              <TabList>
                <Tab>All Methods ({paymentMethods.length})</Tab>
                <Tab>PayPal ({paymentMethods.filter(m => m.method_type === 'paypal').length})</Tab>
                <Tab>Cash App ({paymentMethods.filter(m => m.method_type === 'cashapp').length})</Tab>
                <Tab>Coinbase ({paymentMethods.filter(m => m.method_type === 'coinbase').length})</Tab>
                <Tab>Other ({paymentMethods.filter(m => !['paypal', 'cashapp', 'coinbase'].includes(m.method_type)).length})</Tab>
              </TabList>
            </Tabs>
          </CardHeader>
          
          <CardBody>
            {isLoading ? (
              <Text>Loading payment methods...</Text>
            ) : (
              <Tabs variant="enclosed" index={activeTab}>
                <TabPanels>
                  <TabPanel p={0}>
                    {paymentMethods.length > 0 ? (
                      <VStack spacing={4} align="stretch">
                        {paymentMethods.map(method => (
                          <Card key={method.id} variant="outline">
                            <CardBody>
                              <Flex justify="space-between" align="center" wrap="wrap">
                                <HStack spacing={4}>
                                  <Icon 
                                    as={getMethodIcon(method.method_type)} 
                                    color={`${getMethodColor(method.method_type)}.500`} 
                                    boxSize={6} 
                                  />
                                  <Box>
                                    <Heading size="sm">{method.nickname}</Heading>
                                    <Text fontSize="sm">{getMethodName(method.method_type)}</Text>
                                    <Text fontSize="sm" color="gray.600">{method.account_identifier}</Text>
                                  </Box>
                                </HStack>
                                <VStack align="flex-end">
                                  {method.is_default && (
                                    <Badge colorScheme="green">Default</Badge>
                                  )}
                                  <HStack spacing={2} mt={2}>
                                    {!method.is_default && (
                                      <Button 
                                        size="xs" 
                                        colorScheme="blue" 
                                        onClick={() => handleSetDefault(method.id)}
                                      >
                                        Set as Default
                                      </Button>
                                    )}
                                    <Button 
                                      size="xs" 
                                      colorScheme="red" 
                                      variant="outline" 
                                      onClick={() => handleDelete(method.id)}
                                    >
                                      Delete
                                    </Button>
                                  </HStack>
                                </VStack>
                              </Flex>
                            </CardBody>
                          </Card>
                        ))}
                      </VStack>
                    ) : (
                      <Box textAlign="center" py={10}>
                        <Heading size="md" mb={4}>No Payment Methods</Heading>
                        <Text mb={6}>You haven't added any payment methods yet.</Text>
                        <Button colorScheme="blue" onClick={onOpen}>
                          Add Payment Method
                        </Button>
                      </Box>
                    )}
                  </TabPanel>
                  
                  {/* PayPal Tab */}
                  <TabPanel p={0}>
                    {paymentMethods.filter(m => m.method_type === 'paypal').length > 0 ? (
                      <VStack spacing={4} align="stretch">
                        {paymentMethods
                          .filter(m => m.method_type === 'paypal')
                          .map(method => (
                            <Card key={method.id} variant="outline">
                              <CardBody>
                                <Flex justify="space-between" align="center" wrap="wrap">
                                  <HStack spacing={4}>
                                    <Icon as={FaPaypal} color="blue.500" boxSize={6} />
                                    <Box>
                                      <Heading size="sm">{method.nickname}</Heading>
                                      <Text fontSize="sm">PayPal</Text>
                                      <Text fontSize="sm" color="gray.600">{method.account_identifier}</Text>
                                    </Box>
                                  </HStack>
                                  <VStack align="flex-end">
                                    {method.is_default && (
                                      <Badge colorScheme="green">Default</Badge>
                                    )}
                                    <HStack spacing={2} mt={2}>
                                      {!method.is_default && (
                                        <Button 
                                          size="xs" 
                                          colorScheme="blue" 
                                          onClick={() => handleSetDefault(method.id)}
                                        >
                                          Set as Default
                                        </Button>
                                      )}
                                      <Button 
                                        size="xs" 
                                        colorScheme="red" 
                                        variant="outline" 
                                        onClick={() => handleDelete(method.id)}
                                      >
                                        Delete
                                      </Button>
                                    </HStack>
                                  </VStack>
                                </Flex>
                              </CardBody>
                            </Card>
                          ))}
                      </VStack>
                    ) : (
                      <Box textAlign="center" py={10}>
                        <Heading size="md" mb={4}>No PayPal Accounts</Heading>
                        <Text mb={6}>You haven't added any PayPal accounts yet.</Text>
                        <Button 
                          colorScheme="blue" 
                          onClick={() => {
                            setFormData(prev => ({ ...prev, method_type: 'paypal' }));
                            onOpen();
                          }}
                        >
                          Add PayPal Account
                        </Button>
                      </Box>
                    )}
                  </TabPanel>
                  
                  {/* Cash App Tab */}
                  <TabPanel p={0}>
                    {paymentMethods.filter(m => m.method_type === 'cashapp').length > 0 ? (
                      <VStack spacing={4} align="stretch">
                        {paymentMethods
                          .filter(m => m.method_type === 'cashapp')
                          .map(method => (
                            <Card key={method.id} variant="outline">
                              <CardBody>
                                <Flex justify="space-between" align="center" wrap="wrap">
                                  <HStack spacing={4}>
                                    <Icon as={FaMobileAlt} color="green.500" boxSize={6} />
                                    <Box>
                                      <Heading size="sm">{method.nickname}</Heading>
                                      <Text fontSize="sm">Cash App</Text>
                                      <Text fontSize="sm" color="gray.600">{method.account_identifier}</Text>
                                    </Box>
                  
(Content truncated due to size limit. Use line ranges to read in chunks)