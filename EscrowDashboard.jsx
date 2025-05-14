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
  Stat,
  StatLabel,
  StatNumber,
  StatHelpText,
  StatArrow,
  StatGroup,
  Progress,
  Alert,
  AlertIcon,
  AlertTitle,
  AlertDescription
} from '@chakra-ui/react';
import { useNavigate } from 'react-router-dom';
import { FaMoneyBillWave, FaLock, FaUnlock, FaCheckCircle, FaExclamationCircle, FaHistory, FaChartLine } from 'react-icons/fa';
import { format } from 'date-fns';

const EscrowDashboard = () => {
  const navigate = useNavigate();
  const toast = useToast();
  const { isOpen, onOpen, onClose } = useDisclosure();
  const [isLoading, setIsLoading] = useState(true);
  const [escrowPayments, setEscrowPayments] = useState([]);
  const [selectedPayment, setSelectedPayment] = useState(null);
  const [processingAction, setProcessingAction] = useState(false);
  
  const cardBg = useColorModeValue('white', 'gray.700');
  const borderColor = useColorModeValue('gray.200', 'gray.600');

  useEffect(() => {
    // In a real implementation, this would be an API call
    // Simulate fetching escrow payments
    setTimeout(() => {
      setEscrowPayments([
        {
          id: '1',
          shift_title: 'ICU Night Shift',
          facility_name: 'Memorial Hospital',
          professional_name: 'Sarah Johnson',
          amount: 540.00,
          payment_date: new Date(2025, 3, 19),
          status: 'in_escrow',
          payment_method: 'PayPal (@cubeloid)',
          escrow_release_date: new Date(2025, 3, 22),
          escrow_percentage_complete: 33,
          notes: 'Regular shift payment'
        },
        {
          id: '2',
          shift_title: 'ER Weekend Coverage',
          facility_name: 'City Medical Center',
          professional_name: 'Jessica Williams',
          amount: 582.00,
          payment_date: new Date(2025, 3, 17),
          status: 'in_escrow',
          payment_method: 'Cash App (@clairuth)',
          escrow_release_date: new Date(2025, 3, 20),
          escrow_percentage_complete: 66,
          notes: 'Urgent shift coverage'
        },
        {
          id: '3',
          shift_title: 'Med-Surg Afternoon',
          facility_name: 'Lakeside Clinic',
          professional_name: 'Robert Garcia',
          amount: 310.00,
          payment_date: new Date(2025, 3, 15),
          status: 'ready_for_release',
          payment_method: 'Coinbase (cubeloid@gmail.com)',
          escrow_release_date: new Date(2025, 3, 18),
          escrow_percentage_complete: 100,
          notes: ''
        },
        {
          id: '4',
          shift_title: 'Pediatric Day Shift',
          facility_name: 'Children\'s Hospital',
          professional_name: 'Michael Chen',
          amount: 425.00,
          payment_date: new Date(2025, 3, 12),
          status: 'released',
          payment_method: 'Zelle (cubeloid@gmail.com)',
          escrow_release_date: new Date(2025, 3, 15),
          release_date: new Date(2025, 3, 15),
          escrow_percentage_complete: 100,
          notes: 'Released on schedule'
        },
        {
          id: '5',
          shift_title: 'Geriatric Night Shift',
          facility_name: 'Sunset Care Home',
          professional_name: 'Emily Rodriguez',
          amount: 390.00,
          payment_date: new Date(2025, 3, 10),
          status: 'dispute_resolved',
          payment_method: 'PayPal (@cubeloid)',
          escrow_release_date: new Date(2025, 3, 13),
          release_date: new Date(2025, 3, 14),
          escrow_percentage_complete: 100,
          notes: 'Dispute resolved in favor of professional'
        }
      ]);
      setIsLoading(false);
    }, 1000);
  }, []);

  const getStatusColor = (status) => {
    switch (status) {
      case 'released':
        return 'green';
      case 'ready_for_release':
        return 'blue';
      case 'in_escrow':
        return 'yellow';
      case 'dispute_pending':
        return 'orange';
      case 'dispute_resolved':
        return 'purple';
      case 'refunded':
        return 'red';
      default:
        return 'gray';
    }
  };

  const getStatusIcon = (status) => {
    switch (status) {
      case 'released':
        return FaCheckCircle;
      case 'ready_for_release':
        return FaUnlock;
      case 'in_escrow':
        return FaLock;
      case 'dispute_pending':
        return FaExclamationCircle;
      case 'dispute_resolved':
        return FaHistory;
      case 'refunded':
        return FaExclamationCircle;
      default:
        return FaMoneyBillWave;
    }
  };

  const getStatusText = (status) => {
    switch (status) {
      case 'released':
        return 'Released';
      case 'ready_for_release':
        return 'Ready for Release';
      case 'in_escrow':
        return 'In Escrow';
      case 'dispute_pending':
        return 'Dispute Pending';
      case 'dispute_resolved':
        return 'Dispute Resolved';
      case 'refunded':
        return 'Refunded';
      default:
        return 'Unknown';
    }
  };

  const handleViewDetails = (payment) => {
    setSelectedPayment(payment);
    onOpen();
  };

  const handleReleasePayment = () => {
    if (!selectedPayment) return;
    
    setProcessingAction(true);
    
    // In a real implementation, this would be an API call to release the payment
    setTimeout(() => {
      // Update the payment status
      const updatedPayments = escrowPayments.map(payment => 
        payment.id === selectedPayment.id 
          ? { 
              ...payment, 
              status: 'released',
              release_date: new Date(),
              notes: payment.notes + ' Released early by administrator.'
            } 
          : payment
      );
      setEscrowPayments(updatedPayments);
      
      // Update the selected payment
      setSelectedPayment({
        ...selectedPayment,
        status: 'released',
        release_date: new Date(),
        notes: selectedPayment.notes + ' Released early by administrator.'
      });
      
      toast({
        title: 'Payment Released',
        description: `Payment of $${selectedPayment.amount.toFixed(2)} has been released to ${selectedPayment.professional_name}.`,
        status: 'success',
        duration: 5000,
        isClosable: true,
      });
      
      setProcessingAction(false);
    }, 1500);
  };

  const handleInitiateDispute = () => {
    if (!selectedPayment) return;
    
    setProcessingAction(true);
    
    // In a real implementation, this would be an API call to initiate a dispute
    setTimeout(() => {
      // Update the payment status
      const updatedPayments = escrowPayments.map(payment => 
        payment.id === selectedPayment.id 
          ? { 
              ...payment, 
              status: 'dispute_pending',
              notes: payment.notes + ' Dispute initiated by administrator.'
            } 
          : payment
      );
      setEscrowPayments(updatedPayments);
      
      // Update the selected payment
      setSelectedPayment({
        ...selectedPayment,
        status: 'dispute_pending',
        notes: selectedPayment.notes + ' Dispute initiated by administrator.'
      });
      
      toast({
        title: 'Dispute Initiated',
        description: `A dispute has been initiated for the payment to ${selectedPayment.professional_name}.`,
        status: 'info',
        duration: 5000,
        isClosable: true,
      });
      
      setProcessingAction(false);
    }, 1500);
  };

  // Calculate escrow statistics
  const totalInEscrow = escrowPayments
    .filter(payment => ['in_escrow', 'ready_for_release'].includes(payment.status))
    .reduce((sum, payment) => sum + payment.amount, 0);
    
  const readyForRelease = escrowPayments
    .filter(payment => payment.status === 'ready_for_release')
    .reduce((sum, payment) => sum + payment.amount, 0);
    
  const releasedThisMonth = escrowPayments
    .filter(payment => 
      payment.status === 'released' && 
      payment.release_date.getMonth() === new Date().getMonth() &&
      payment.release_date.getFullYear() === new Date().getFullYear()
    )
    .reduce((sum, payment) => sum + payment.amount, 0);

  return (
    <Box p={4}>
      <VStack spacing={8} align="stretch">
        <Flex 
          direction={{ base: 'column', md: 'row' }} 
          justify="space-between" 
          align={{ base: 'flex-start', md: 'center' }}
        >
          <Box>
            <Heading size="lg">Escrow Dashboard</Heading>
            <Text color="gray.600">Monitor and manage payments in escrow</Text>
          </Box>
        </Flex>
        
        {/* Escrow Statistics */}
        <StatGroup>
          <Card bg={cardBg} borderWidth="1px" borderColor={borderColor} borderRadius="lg" shadow="md" flex={1}>
            <CardBody>
              <Stat>
                <StatLabel>Total in Escrow</StatLabel>
                <StatNumber>${totalInEscrow.toFixed(2)}</StatNumber>
                <StatHelpText>
                  <HStack>
                    <Icon as={FaLock} color="yellow.500" />
                    <Text>Funds secured</Text>
                  </HStack>
                </StatHelpText>
              </Stat>
            </CardBody>
          </Card>
          
          <Card bg={cardBg} borderWidth="1px" borderColor={borderColor} borderRadius="lg" shadow="md" flex={1}>
            <CardBody>
              <Stat>
                <StatLabel>Ready for Release</StatLabel>
                <StatNumber>${readyForRelease.toFixed(2)}</StatNumber>
                <StatHelpText>
                  <HStack>
                    <Icon as={FaUnlock} color="blue.500" />
                    <Text>Escrow period complete</Text>
                  </HStack>
                </StatHelpText>
              </Stat>
            </CardBody>
          </Card>
          
          <Card bg={cardBg} borderWidth="1px" borderColor={borderColor} borderRadius="lg" shadow="md" flex={1}>
            <CardBody>
              <Stat>
                <StatLabel>Released This Month</StatLabel>
                <StatNumber>${releasedThisMonth.toFixed(2)}</StatNumber>
                <StatHelpText>
                  <HStack>
                    <Icon as={FaChartLine} color="green.500" />
                    <Text>Completed payments</Text>
                  </HStack>
                </StatHelpText>
              </Stat>
            </CardBody>
          </Card>
        </StatGroup>
        
        {/* Payments Ready for Release Alert */}
        {escrowPayments.filter(payment => payment.status === 'ready_for_release').length > 0 && (
          <Alert status="info" borderRadius="md">
            <AlertIcon />
            <AlertTitle mr={2}>Payments Ready for Release</AlertTitle>
            <AlertDescription>
              {escrowPayments.filter(payment => payment.status === 'ready_for_release').length} payment(s) have completed the escrow period and are ready to be released.
            </AlertDescription>
          </Alert>
        )}
        
        {/* Escrow Payments Table */}
        <Card bg={cardBg} borderWidth="1px" borderColor={borderColor} borderRadius="lg" shadow="md">
          <CardHeader>
            <Heading size="md">Escrow Payments</Heading>
          </CardHeader>
          
          <CardBody>
            {isLoading ? (
              <Flex justify="center" p={4}>
                <Spinner size="lg" />
              </Flex>
            ) : escrowPayments.length > 0 ? (
              <Table variant="simple" size={{ base: "sm", md: "md" }}>
                <Thead>
                  <Tr>
                    <Th>Date</Th>
                    <Th>Shift / Professional</Th>
                    <Th>Facility</Th>
                    <Th isNumeric>Amount</Th>
                    <Th>Status</Th>
                    <Th>Escrow Progress</Th>
                    <Th>Actions</Th>
                  </Tr>
                </Thead>
                <Tbody>
                  {escrowPayments.map(payment => (
                    <Tr key={payment.id}>
                      <Td>
                        <Text>{format(payment.payment_date, 'MMM d, yyyy')}</Text>
                      </Td>
                      <Td>
                        <Text fontWeight="bold">{payment.shift_title}</Text>
                        <Text fontSize="sm">{payment.professional_name}</Text>
                      </Td>
                      <Td>
                        <Text fontSize="sm">{payment.facility_name}</Text>
                      </Td>
                      <Td isNumeric fontWeight="bold">${payment.amount.toFixed(2)}</Td>
                      <Td>
                        <HStack>
                          <Icon 
                            as={getStatusIcon(payment.status)} 
                            color={`${getStatusColor(payment.status)}.500`} 
                          />
                          <Badge colorScheme={getStatusColor(payment.status)}>
                            {getStatusText(payment.status)}
                          </Badge>
                        </HStack>
                      </Td>
                      <Td>
                        {['in_escrow', 'ready_for_release'].includes(payment.status) && (
                          <VStack spacing={1} align="stretch">
                            <Progress 
                              value={payment.escrow_percentage_complete} 
                              size="sm" 
                              colorScheme={payment.escrow_percentage_complete === 100 ? "green" : "blue"} 
                              borderRadius="md"
                            />
                            <Text fontSize="xs">
                              Release: {format(payment.escrow_release_date, 'MMM d, yyyy')}
                            </Text>
                          </VStack>
                        )}
                        {payment.status === 'released' && (
                          <Text fontSize="xs">
                            Released: {format(payment.release_date, 'MMM d, yyyy')}
                          </Text>
                        )}
                      </Td>
                      <Td>
                        <Button 
                          size="sm" 
                          colorScheme="blue" 
                          onClick={() => handleViewDetails(payment)}
                        >
                          Details
                        </Button>
                      </Td>
                    </Tr>
                  ))}
                </Tbody>
              </Table>
            ) : (
              <Box textAlign="center" py={6}>
                <Heading size="sm" mb={2}>No Escrow Payments</Heading>
                <Text>There are no payments in the escrow system.</Text>
              </Box>
            )}
          </CardBody>
          
          <CardFooter>
            <Text fontSize="sm" color="gray.600">
              The escrow system holds payments for 3 days to ensure shifts are completed satisfactorily.
              Payments are automatically released after the escrow period unless a dispute is initiated.
            </Text>
          </CardFooter>
        </Card>
      </VStack>
      
      {/* Payment Details Modal */}
      <Modal isOpen={isOpen} onClose={onClose} size="lg">
        <ModalOverlay />
        <ModalContent>
          <ModalHeader>Payment Details</ModalHeader>
          <ModalCloseButton />
          <ModalBody>
            {selectedPayment && (
              <VStack spacing={6} align="stretch">
                <Flex justify="space-between" align="center">
                  <Heading size="md">{selectedPayment.shift_title}</
(Content truncated due to size limit. Use line ranges to read in chunks)