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
  Select,
  Textarea,
  Progress,
  SimpleGrid,
  Stat,
  StatLabel,
  StatNumber,
  StatHelpText,
  StatArrow,
  StatGroup
} from '@chakra-ui/react';
import { useNavigate } from 'react-router-dom';
import { FaMoneyBillWave, FaPaypal, FaBitcoin, FaMobileAlt, FaChartLine, FaExclamationTriangle, FaCheckCircle } from 'react-icons/fa';
import { format } from 'date-fns';

const PaymentReports = () => {
  const navigate = useNavigate();
  const toast = useToast();
  const { isOpen, onOpen, onClose } = useDisclosure();
  const [isLoading, setIsLoading] = useState(true);
  const [reportData, setReportData] = useState(null);
  const [selectedReport, setSelectedReport] = useState('monthly');
  const [selectedMonth, setSelectedMonth] = useState(new Date().getMonth());
  const [selectedYear, setSelectedYear] = useState(new Date().getFullYear());
  const [selectedPaymentMethod, setSelectedPaymentMethod] = useState('all');
  const [isGeneratingReport, setIsGeneratingReport] = useState(false);
  
  const cardBg = useColorModeValue('white', 'gray.700');
  const borderColor = useColorModeValue('gray.200', 'gray.600');

  useEffect(() => {
    // In a real implementation, this would be an API call to fetch report data
    generateReport();
  }, []);

  const generateReport = () => {
    setIsLoading(true);
    setIsGeneratingReport(true);
    
    // In a real implementation, this would be an API call with the selected filters
    setTimeout(() => {
      // Mock data for demonstration
      const mockData = {
        summary: {
          total_payments: 42,
          total_amount: 18750.50,
          average_payment: 446.44,
          escrow_amount: 3120.00,
          dispute_rate: 2.4,
          payment_methods: {
            paypal: { count: 18, amount: 8250.25, percentage: 44 },
            cashapp: { count: 12, amount: 5100.75, percentage: 27 },
            coinbase: { count: 8, amount: 3600.50, percentage: 19 },
            zelle: { count: 4, amount: 1799.00, percentage: 10 }
          }
        },
        monthly_trend: [
          { month: 'Jan', amount: 12500.00 },
          { month: 'Feb', amount: 14200.00 },
          { month: 'Mar', amount: 15800.00 },
          { month: 'Apr', amount: 18750.50 }
        ],
        payments: [
          {
            id: '1',
            date: new Date(2025, 3, 19),
            shift_title: 'ICU Night Shift',
            facility_name: 'Memorial Hospital',
            professional_name: 'Sarah Johnson',
            amount: 540.00,
            status: 'completed',
            payment_method: 'PayPal'
          },
          {
            id: '2',
            date: new Date(2025, 3, 18),
            shift_title: 'ER Weekend Coverage',
            facility_name: 'City Medical Center',
            professional_name: 'Jessica Williams',
            amount: 582.00,
            status: 'in_escrow',
            payment_method: 'Cash App'
          },
          {
            id: '3',
            date: new Date(2025, 3, 17),
            shift_title: 'Med-Surg Afternoon',
            facility_name: 'Lakeside Clinic',
            professional_name: 'Robert Garcia',
            amount: 310.00,
            status: 'completed',
            payment_method: 'Coinbase'
          },
          {
            id: '4',
            date: new Date(2025, 3, 16),
            shift_title: 'Pediatric Day Shift',
            facility_name: 'Children\'s Hospital',
            professional_name: 'Michael Chen',
            amount: 425.00,
            status: 'completed',
            payment_method: 'Zelle'
          },
          {
            id: '5',
            date: new Date(2025, 3, 15),
            shift_title: 'Geriatric Night Shift',
            facility_name: 'Sunset Care Home',
            professional_name: 'Emily Rodriguez',
            amount: 390.00,
            status: 'dispute_resolved',
            payment_method: 'PayPal'
          }
        ]
      };
      
      setReportData(mockData);
      setIsLoading(false);
      setIsGeneratingReport(false);
      
      toast({
        title: 'Report Generated',
        description: 'Payment report has been generated successfully.',
        status: 'success',
        duration: 3000,
        isClosable: true,
      });
    }, 1500);
  };

  const handleReportTypeChange = (e) => {
    setSelectedReport(e.target.value);
  };

  const handleMonthChange = (e) => {
    setSelectedMonth(parseInt(e.target.value));
  };

  const handleYearChange = (e) => {
    setSelectedYear(parseInt(e.target.value));
  };

  const handlePaymentMethodChange = (e) => {
    setSelectedPaymentMethod(e.target.value);
  };

  const handleGenerateReport = () => {
    generateReport();
  };

  const handleExportCSV = () => {
    setIsGeneratingReport(true);
    
    // In a real implementation, this would generate and download a CSV file
    setTimeout(() => {
      toast({
        title: 'Report Exported',
        description: 'Payment report has been exported as CSV.',
        status: 'success',
        duration: 3000,
        isClosable: true,
      });
      setIsGeneratingReport(false);
    }, 1000);
  };

  const handleExportPDF = () => {
    setIsGeneratingReport(true);
    
    // In a real implementation, this would generate and download a PDF file
    setTimeout(() => {
      toast({
        title: 'Report Exported',
        description: 'Payment report has been exported as PDF.',
        status: 'success',
        duration: 3000,
        isClosable: true,
      });
      setIsGeneratingReport(false);
    }, 1000);
  };

  const getStatusColor = (status) => {
    switch (status) {
      case 'completed':
        return 'green';
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

  const getMethodIcon = (method) => {
    switch (method.toLowerCase()) {
      case 'paypal':
        return FaPaypal;
      case 'cash app':
        return FaMobileAlt;
      case 'coinbase':
        return FaBitcoin;
      case 'zelle':
        return FaMobileAlt;
      default:
        return FaMoneyBillWave;
    }
  };

  const getMethodColor = (method) => {
    switch (method.toLowerCase()) {
      case 'paypal':
        return 'blue';
      case 'cash app':
        return 'green';
      case 'coinbase':
        return 'orange';
      case 'zelle':
        return 'purple';
      default:
        return 'gray';
    }
  };

  const months = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
  ];

  const currentYear = new Date().getFullYear();
  const years = [currentYear - 2, currentYear - 1, currentYear];

  return (
    <Box p={4}>
      <VStack spacing={8} align="stretch">
        <Flex 
          direction={{ base: 'column', md: 'row' }} 
          justify="space-between" 
          align={{ base: 'flex-start', md: 'center' }}
        >
          <Box>
            <Heading size="lg">Payment Reports</Heading>
            <Text color="gray.600">Generate and analyze payment reports</Text>
          </Box>
        </Flex>
        
        {/* Report Filters */}
        <Card bg={cardBg} borderWidth="1px" borderColor={borderColor} borderRadius="lg" shadow="md">
          <CardHeader>
            <Heading size="md">Report Filters</Heading>
          </CardHeader>
          
          <CardBody>
            <SimpleGrid columns={{ base: 1, md: 4 }} spacing={4}>
              <FormControl>
                <FormLabel>Report Type</FormLabel>
                <Select
                  value={selectedReport}
                  onChange={handleReportTypeChange}
                >
                  <option value="daily">Daily Report</option>
                  <option value="weekly">Weekly Report</option>
                  <option value="monthly">Monthly Report</option>
                  <option value="yearly">Yearly Report</option>
                  <option value="custom">Custom Range</option>
                </Select>
              </FormControl>
              
              <FormControl>
                <FormLabel>Month</FormLabel>
                <Select
                  value={selectedMonth}
                  onChange={handleMonthChange}
                  isDisabled={selectedReport === 'daily' || selectedReport === 'weekly' || selectedReport === 'custom'}
                >
                  {months.map((month, index) => (
                    <option key={index} value={index}>{month}</option>
                  ))}
                </Select>
              </FormControl>
              
              <FormControl>
                <FormLabel>Year</FormLabel>
                <Select
                  value={selectedYear}
                  onChange={handleYearChange}
                  isDisabled={selectedReport === 'daily' || selectedReport === 'weekly' || selectedReport === 'custom'}
                >
                  {years.map(year => (
                    <option key={year} value={year}>{year}</option>
                  ))}
                </Select>
              </FormControl>
              
              <FormControl>
                <FormLabel>Payment Method</FormLabel>
                <Select
                  value={selectedPaymentMethod}
                  onChange={handlePaymentMethodChange}
                >
                  <option value="all">All Methods</option>
                  <option value="paypal">PayPal</option>
                  <option value="cashapp">Cash App</option>
                  <option value="coinbase">Coinbase</option>
                  <option value="zelle">Zelle</option>
                </Select>
              </FormControl>
            </SimpleGrid>
          </CardBody>
          
          <CardFooter>
            <Button 
              colorScheme="blue" 
              onClick={handleGenerateReport}
              isLoading={isGeneratingReport}
              leftIcon={<Icon as={FaChartLine} />}
            >
              Generate Report
            </Button>
          </CardFooter>
        </Card>
        
        {isLoading ? (
          <Flex justify="center" p={10}>
            <Spinner size="xl" />
          </Flex>
        ) : reportData && (
          <>
            {/* Report Summary */}
            <Card bg={cardBg} borderWidth="1px" borderColor={borderColor} borderRadius="lg" shadow="md">
              <CardHeader>
                <Heading size="md">
                  Payment Summary - {months[selectedMonth]} {selectedYear}
                </Heading>
              </CardHeader>
              
              <CardBody>
                <SimpleGrid columns={{ base: 1, md: 3 }} spacing={6}>
                  <StatGroup>
                    <Stat>
                      <StatLabel>Total Payments</StatLabel>
                      <StatNumber>{reportData.summary.total_payments}</StatNumber>
                      <StatHelpText>
                        <StatArrow type="increase" />
                        8.2% from last month
                      </StatHelpText>
                    </Stat>
                    
                    <Stat>
                      <StatLabel>Total Amount</StatLabel>
                      <StatNumber>${reportData.summary.total_amount.toFixed(2)}</StatNumber>
                      <StatHelpText>
                        <StatArrow type="increase" />
                        18.7% from last month
                      </StatHelpText>
                    </Stat>
                  </StatGroup>
                  
                  <StatGroup>
                    <Stat>
                      <StatLabel>Average Payment</StatLabel>
                      <StatNumber>${reportData.summary.average_payment.toFixed(2)}</StatNumber>
                      <StatHelpText>
                        <StatArrow type="increase" />
                        5.3% from last month
                      </StatHelpText>
                    </Stat>
                    
                    <Stat>
                      <StatLabel>In Escrow</StatLabel>
                      <StatNumber>${reportData.summary.escrow_amount.toFixed(2)}</StatNumber>
                      <StatHelpText>
                        <Icon as={FaMoneyBillWave} color="yellow.500" mr={1} />
                        Pending release
                      </StatHelpText>
                    </Stat>
                  </StatGroup>
                  
                  <StatGroup>
                    <Stat>
                      <StatLabel>Dispute Rate</StatLabel>
                      <StatNumber>{reportData.summary.dispute_rate}%</StatNumber>
                      <StatHelpText>
                        <StatArrow type="decrease" />
                        1.2% from last month
                      </StatHelpText>
                    </Stat>
                    
                    <Stat>
                      <StatLabel>Successful Payments</StatLabel>
                      <StatNumber>97.6%</StatNumber>
                      <StatHelpText>
                        <Icon as={FaCheckCircle} color="green.500" mr={1} />
                        High success rate
                      </StatHelpText>
                    </Stat>
                  </StatGroup>
                </SimpleGrid>
                
                <Divider my={6} />
                
                <Heading size="sm" mb={4}>Payment Method Breakdown</Heading>
                
                <SimpleGrid columns={{ base: 1, md: 4 }} spacing={4}>
                  {Object.entries(reportData.summary.payment_methods).map(([method, data]) => (
                    <Box key={method} p={4} borderWidth="1px" borderRadius="md" bg={`${getMethodColor(method)}.50`}>
                      <HStack mb={2}>
                        <Icon as={getMethodIcon(method)} color={`${getMethodColor(method)}.500`} />
                        <Heading size="sm">{method === 'cashapp' ? 'Cash App' : method.charAt(0).toUpperCase() + method.slice(1)}</Heading>
                      </HStack>
                      <Text fontWeight="bold">${data.amount.toFixed(2)}</Text>
                      <Text fontSize="sm">{data.count} payments ({data.percentage}%)</Text>
                      <Progress value={data.percentage} size="sm" colorScheme={getMethodColor(method)} mt={2} />
                    </Box>
                  ))}
                </SimpleGrid>
                
                <Divider my={6} />
                
                <Heading size="sm" mb={4}>Monthly Trend</Heading>
                
                <Box p={4} borderWidth="1px" borderRadius="md" bg="gray.50">
                  <Flex h="200px" align="flex-end">
                    {reportData.monthly_trend.map((month, index) => (
                      <VStack key={index} flex={1} h="100%" justify="flex-end" spacing={2}>
                        <Box 
                          w="70%" 
                          bg="blue.500" 
                          h={`${(month.amount / 20000) * 100}%`} 
                          borderTopRadius="md"
                        />
                        <Text fontSize="sm">{month.month}</Text>
                        <Text fontSize="xs" fontWeight="bold">${(month.amount / 1000).toFixed(1)}k</Text>
                      </VStack>
                    ))}
                  </Flex>
                </Box>
              </CardBody>
              
              <C
(Content truncated due to size limit. Use line ranges to read in chunks)