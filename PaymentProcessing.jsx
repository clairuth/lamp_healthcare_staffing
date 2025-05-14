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
  Select,
  FormControl,
  FormLabel,
  Input,
  Spinner
} from '@chakra-ui/react';
import { useNavigate } from 'react-router-dom';
import { FaMoneyBillWave, FaCreditCard, FaPaypal, FaBitcoin, FaMobileAlt, FaLock, FaCheckCircle, FaExclamationCircle } from 'react-icons/fa';
import { format } from 'date-fns';

const PaymentProcessing = () => {
  const navigate = useNavigate();
  const toast = useToast();
  const { isOpen, onOpen, onClose } = useDisclosure();
  const [isLoading, setIsLoading] = useState(true);
  const [payments, setPayments] = useState([]);
  const [paymentMethods, setPaymentMethods] = useState([]);
  const [selectedShift, setSelectedShift] = useState(null);
  const [completedShifts, setCompletedShifts] = useState([]);
  const [processingPayment, setProcessingPayment] = useState(false);
  
  const cardBg = useColorModeValue('white', 'gray.700');
  const borderColor = useColorModeValue('gray.200', 'gray.600');
  
  const [formData, setFormData] = useState({
    shift_id: '',
    payment_method_id: '',
    amount: '',
    notes: ''
  });

  useEffect(() => {
    // In a real implementation, these would be API calls
    // Simulate fetching payments and payment methods
    const fetchData = async () => {
      try {
        // Simulate API calls with timeout
        setTimeout(() => {
          // Mock data for demonstration
          setPayments([
            {
              id: '1',
              shift_title: 'ICU Night Shift',
              professional_name: 'Sarah Johnson',
              amount: 360.00,
              payment_date: new Date(2025, 3, 15),
              status: 'completed',
              payment_method: 'PayPal (@cubeloid)',
              escrow_release_date: new Date(2025, 3, 18)
            },
            {
              id: '2',
              shift_title: 'ER Morning Shift',
              professional_name: 'David Miller',
              amount: 297.50,
              payment_date: new Date(2025, 3, 10),
              status: 'in_escrow',
              payment_method: 'Cash App (@clairuth)',
              escrow_release_date: new Date(2025, 3, 13)
            },
            {
              id: '3',
              shift_title: 'Med-Surg Afternoon',
              professional_name: 'Robert Garcia',
              amount: 310.00,
              payment_date: new Date(2025, 3, 5),
              status: 'completed',
              payment_method: 'Coinbase (cubeloid@gmail.com)',
              escrow_release_date: new Date(2025, 3, 8)
            }
          ]);
          
          setPaymentMethods([
            {
              id: '1',
              method_type: 'paypal',
              account_identifier: '@cubeloid',
              nickname: 'My PayPal',
              is_default: true
            },
            {
              id: '2',
              method_type: 'cashapp',
              account_identifier: '@clairuth',
              nickname: 'CashApp Account',
              is_default: false
            },
            {
              id: '3',
              method_type: 'coinbase',
              account_identifier: 'cubeloid@gmail.com',
              nickname: 'Coinbase',
              is_default: false
            },
            {
              id: '4',
              method_type: 'zelle',
              account_identifier: 'cubeloid@gmail.com',
              nickname: 'Zelle',
              is_default: false
            }
          ]);
          
          setCompletedShifts([
            {
              id: '1',
              title: 'ICU Night Shift',
              professional_name: 'Michael Chen',
              professional_id: '101',
              start_time: new Date(2025, 3, 19, 19, 0),
              end_time: new Date(2025, 3, 20, 7, 0),
              hourly_rate: 45.00,
              total_hours: 12,
              total_amount: 540.00,
              status: 'completed',
              payment_status: 'unpaid'
            },
            {
              id: '2',
              title: 'ER Weekend Coverage',
              professional_name: 'Jessica Williams',
              professional_id: '102',
              start_time: new Date(2025, 3, 18, 7, 0),
              end_time: new Date(2025, 3, 18, 19, 0),
              hourly_rate: 48.50,
              total_hours: 12,
              total_amount: 582.00,
              status: 'completed',
              payment_status: 'unpaid'
            }
          ]);
          
          setIsLoading(false);
        }, 1000);
      } catch (error) {
        console.error('Error fetching data:', error);
        setIsLoading(false);
      }
    };

    fetchData();
  }, []);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: value
    }));
    
    if (name === 'shift_id' && value) {
      const shift = completedShifts.find(s => s.id === value);
      if (shift) {
        setSelectedShift(shift);
        setFormData(prev => ({
          ...prev,
          amount: shift.total_amount.toFixed(2)
        }));
      }
    }
  };

  const handleCreatePayment = () => {
    setProcessingPayment(true);
    
    // In a real implementation, this would be an API call to create the payment
    setTimeout(() => {
      // Generate a new ID
      const newId = (payments.length + 1).toString();
      
      // Get the selected payment method
      const paymentMethod = paymentMethods.find(m => m.id === formData.payment_method_id);
      
      // Create new payment
      const newPayment = {
        id: newId,
        shift_title: selectedShift.title,
        professional_name: selectedShift.professional_name,
        amount: parseFloat(formData.amount),
        payment_date: new Date(),
        status: 'in_escrow',
        payment_method: `${getMethodName(paymentMethod.method_type)} (${paymentMethod.account_identifier})`,
        escrow_release_date: new Date(new Date().setDate(new Date().getDate() + 3))
      };
      
      // Add the new payment
      setPayments([newPayment, ...payments]);
      
      // Update the completed shift's payment status
      const updatedShifts = completedShifts.map(shift => 
        shift.id === selectedShift.id 
          ? { ...shift, payment_status: 'paid' } 
          : shift
      );
      setCompletedShifts(updatedShifts);
      
      toast({
        title: 'Payment Created',
        description: `Payment of $${formData.amount} has been created and is now in escrow.`,
        status: 'success',
        duration: 5000,
        isClosable: true,
      });
      
      // Reset form and close modal
      setFormData({
        shift_id: '',
        payment_method_id: '',
        amount: '',
        notes: ''
      });
      setSelectedShift(null);
      onClose();
      setProcessingPayment(false);
    }, 2000);
  };

  const getStatusColor = (status) => {
    switch (status) {
      case 'completed':
        return 'green';
      case 'in_escrow':
        return 'yellow';
      case 'failed':
        return 'red';
      case 'refunded':
        return 'red';
      default:
        return 'gray';
    }
  };

  const getStatusIcon = (status) => {
    switch (status) {
      case 'completed':
        return FaCheckCircle;
      case 'in_escrow':
        return FaLock;
      case 'failed':
        return FaExclamationCircle;
      case 'refunded':
        return FaExclamationCircle;
      default:
        return FaMoneyBillWave;
    }
  };

  const getMethodIcon = (methodType) => {
    if (methodType.includes('paypal')) return FaPaypal;
    if (methodType.includes('cashapp')) return FaMobileAlt;
    if (methodType.includes('coinbase')) return FaBitcoin;
    if (methodType.includes('zelle')) return FaMobileAlt;
    if (methodType.includes('bank')) return FaMoneyBillWave;
    if (methodType.includes('credit')) return FaCreditCard;
    return FaMoneyBillWave;
  };

  const getMethodColor = (methodType) => {
    if (methodType.includes('paypal')) return 'blue';
    if (methodType.includes('cashapp')) return 'green';
    if (methodType.includes('coinbase')) return 'orange';
    if (methodType.includes('zelle')) return 'purple';
    if (methodType.includes('bank')) return 'teal';
    if (methodType.includes('credit')) return 'red';
    return 'gray';
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

  const handleReleaseFromEscrow = (paymentId) => {
    setIsLoading(true);
    
    // In a real implementation, this would be an API call to release the payment from escrow
    setTimeout(() => {
      // Update the payment status
      const updatedPayments = payments.map(payment => 
        payment.id === paymentId 
          ? { ...payment, status: 'completed' } 
          : payment
      );
      setPayments(updatedPayments);
      
      toast({
        title: 'Payment Released',
        description: 'Payment has been released from escrow.',
        status: 'success',
        duration: 5000,
        isClosable: true,
      });
      
      setIsLoading(false);
    }, 1500);
  };

  const handleRefundPayment = (paymentId) => {
    setIsLoading(true);
    
    // In a real implementation, this would be an API call to refund the payment
    setTimeout(() => {
      // Update the payment status
      const updatedPayments = payments.map(payment => 
        payment.id === paymentId 
          ? { ...payment, status: 'refunded' } 
          : payment
      );
      setPayments(updatedPayments);
      
      toast({
        title: 'Payment Refunded',
        description: 'Payment has been refunded.',
        status: 'success',
        duration: 5000,
        isClosable: true,
      });
      
      setIsLoading(false);
    }, 1500);
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
            <Heading size="lg">Payment Processing</Heading>
            <Text color="gray.600">Manage payments for completed shifts</Text>
          </Box>
          
          <Button 
            colorScheme="green" 
            onClick={onOpen}
            leftIcon={<Icon as={FaMoneyBillWave} />}
            mt={{ base: 4, md: 0 }}
            isDisabled={completedShifts.filter(s => s.payment_status === 'unpaid').length === 0}
          >
            Create New Payment
          </Button>
        </Flex>
        
        {/* Completed Shifts Awaiting Payment */}
        <Card bg={cardBg} borderWidth="1px" borderColor={borderColor} borderRadius="lg" shadow="md">
          <CardHeader>
            <Heading size="md">Shifts Awaiting Payment</Heading>
          </CardHeader>
          
          <CardBody>
            {isLoading ? (
              <Flex justify="center" p={4}>
                <Spinner size="lg" />
              </Flex>
            ) : completedShifts.filter(s => s.payment_status === 'unpaid').length > 0 ? (
              <Table variant="simple" size={{ base: "sm", md: "md" }}>
                <Thead>
                  <Tr>
                    <Th>Shift</Th>
                    <Th>Professional</Th>
                    <Th isNumeric>Hours</Th>
                    <Th isNumeric>Rate</Th>
                    <Th isNumeric>Total</Th>
                    <Th>Action</Th>
                  </Tr>
                </Thead>
                <Tbody>
                  {completedShifts
                    .filter(shift => shift.payment_status === 'unpaid')
                    .map(shift => (
                      <Tr key={shift.id}>
                        <Td>
                          <Text fontWeight="bold">{shift.title}</Text>
                          <Text fontSize="xs">{format(shift.start_time, 'MMM d, yyyy')}</Text>
                        </Td>
                        <Td>{shift.professional_name}</Td>
                        <Td isNumeric>{shift.total_hours}</Td>
                        <Td isNumeric>${shift.hourly_rate.toFixed(2)}</Td>
                        <Td isNumeric fontWeight="bold">${shift.total_amount.toFixed(2)}</Td>
                        <Td>
                          <Button 
                            size="sm" 
                            colorScheme="green"
                            onClick={() => {
                              setSelectedShift(shift);
                              setFormData({
                                shift_id: shift.id,
                                payment_method_id: paymentMethods.find(m => m.is_default)?.id || '',
                                amount: shift.total_amount.toFixed(2),
                                notes: ''
                              });
                              onOpen();
                            }}
                          >
                            Pay Now
                          </Button>
                        </Td>
                      </Tr>
                    ))}
                </Tbody>
              </Table>
            ) : (
              <Box textAlign="center" py={6}>
                <Heading size="sm" mb={2}>No Shifts Awaiting Payment</Heading>
                <Text>All completed shifts have been paid.</Text>
              </Box>
            )}
          </CardBody>
        </Card>
        
        {/* Payment History */}
        <Card bg={cardBg} borderWidth="1px" borderColor={borderColor} borderRadius="lg" shadow="md">
          <CardHeader>
            <Heading size="md">Payment History</Heading>
          </CardHeader>
          
          <CardBody>
            {isLoading ? (
              <Flex justify="center" p={4}>
                <Spinner size="lg" />
              </Flex>
            ) : payments.length > 0 ? (
              <Table variant="simple" size={{ base: "sm", md: "md" }}>
                <Thead>
                  <Tr>
                    <Th>Date</Th>
                    <Th>Shift / Professional</Th>
                    <Th>Payment Method</Th>
                    <Th isNumeric>Amount</Th>
                    <Th>Status</Th>
                    <Th>Actions</Th>
                  </Tr>
                </Thead>
                <Tbody>
                  {payments.map(payment => (
                    <Tr key={payment.id}>
                      <Td>
                        <Text>{format(payment.payment_date, 'MMM d, yyyy')}</Text>
                      </Td>
                      <Td>
                        <Text fontWeight="bold">{payment.shift_title}</Text>
                        <Text fontSize="sm">{payment.professional_name}</Text>
                      </Td>
                      <Td>
                        <HStack>
                          <Icon 
                            as={getMethodIcon(payment.payment_method.toLowerCase())} 
                            color={`${getMethodColor(payment.payment_method.toLowerCase())}.500`} 
                          />
                          <Text fontSize="sm">{payment.payment_method}</Text>
                        </HStack>
                      </Td>
                      <Td isNumeric fontWeight="bold">${payment.amount.toFixed(2)}</Td>
                      <Td>
                        <HStack>
                    
(Content truncated due to size limit. Use line ranges to read in chunks)