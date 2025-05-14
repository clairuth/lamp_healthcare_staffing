import React, { useState, useEffect } from 'react';
import { 
  Box, 
  Flex, 
  Heading, 
  Text, 
  VStack, 
  HStack, 
  Grid, 
  GridItem, 
  Card, 
  CardHeader, 
  CardBody, 
  CardFooter, 
  Button, 
  Badge, 
  Avatar, 
  Stat, 
  StatLabel, 
  StatNumber, 
  StatHelpText,
  useColorModeValue,
  Icon,
  SimpleGrid,
  Table,
  Thead,
  Tbody,
  Tr,
  Th,
  Td
} from '@chakra-ui/react';
import { useAuth } from '../../contexts/AuthContext';
import { useNavigate } from 'react-router-dom';
import { FaCalendarAlt, FaMoneyBillWave, FaUserNurse, FaHospital, FaClipboardCheck } from 'react-icons/fa';
import { format } from 'date-fns';

const FacilityDashboard = () => {
  const { user } = useAuth();
  const navigate = useNavigate();
  const [activeShifts, setActiveShifts] = useState([]);
  const [applications, setApplications] = useState([]);
  const [recentPayments, setRecentPayments] = useState([]);
  const [isLoading, setIsLoading] = useState(true);

  const cardBg = useColorModeValue('white', 'gray.700');
  const borderColor = useColorModeValue('gray.200', 'gray.600');
  const statBg = useColorModeValue('green.50', 'green.900');

  useEffect(() => {
    // In a real implementation, these would be API calls
    const fetchDashboardData = async () => {
      try {
        // Simulate API calls with timeout
        setTimeout(() => {
          // Mock data for demonstration
          setActiveShifts([
            {
              id: '1',
              title: 'ICU Night Shift',
              required_role: 'RN',
              start_time: new Date(2025, 3, 22, 19, 0),
              end_time: new Date(2025, 3, 23, 7, 0),
              hourly_rate: 45.00,
              status: 'open',
              applications_count: 3
            },
            {
              id: '2',
              title: 'ER Morning Shift',
              required_role: 'ER RN',
              start_time: new Date(2025, 3, 25, 7, 0),
              end_time: new Date(2025, 3, 25, 15, 0),
              hourly_rate: 42.50,
              status: 'filled',
              applications_count: 5,
              assigned_professional: 'Sarah Johnson'
            },
            {
              id: '3',
              title: 'Med-Surg Afternoon',
              required_role: 'LPN',
              start_time: new Date(2025, 3, 21, 15, 0),
              end_time: new Date(2025, 3, 21, 23, 0),
              hourly_rate: 38.75,
              status: 'open',
              applications_count: 2
            }
          ]);
          
          setApplications([
            {
              id: '1',
              shift_id: '1',
              shift_title: 'ICU Night Shift',
              professional_name: 'Michael Chen',
              professional_type: 'RN',
              years_experience: 5,
              application_date: new Date(2025, 3, 18),
              status: 'pending'
            },
            {
              id: '2',
              shift_id: '1',
              shift_title: 'ICU Night Shift',
              professional_name: 'Jessica Williams',
              professional_type: 'RN',
              years_experience: 7,
              application_date: new Date(2025, 3, 19),
              status: 'pending'
            },
            {
              id: '3',
              shift_id: '3',
              shift_title: 'Med-Surg Afternoon',
              professional_name: 'Robert Garcia',
              professional_type: 'LPN',
              years_experience: 3,
              application_date: new Date(2025, 3, 20),
              status: 'pending'
            }
          ]);
          
          setRecentPayments([
            {
              id: '1',
              amount: 360.00,
              date: new Date(2025, 3, 15),
              professional: 'Sarah Johnson',
              shift: 'ICU Night Shift (Apr 14)',
              status: 'completed'
            },
            {
              id: '2',
              amount: 297.50,
              date: new Date(2025, 3, 10),
              professional: 'David Miller',
              shift: 'ER Morning Shift (Apr 9)',
              status: 'completed'
            }
          ]);
          
          setIsLoading(false);
        }, 1000);
      } catch (error) {
        console.error('Error fetching dashboard data:', error);
        setIsLoading(false);
      }
    };

    fetchDashboardData();
  }, []);

  const getStatusColor = (status) => {
    switch (status) {
      case 'open':
        return 'blue';
      case 'filled':
        return 'green';
      case 'in_progress':
        return 'purple';
      case 'completed':
        return 'teal';
      case 'cancelled':
        return 'red';
      case 'pending':
        return 'yellow';
      default:
        return 'gray';
    }
  };

  const calculateTotalSpent = () => {
    return recentPayments.reduce((total, payment) => total + payment.amount, 0).toFixed(2);
  };

  const calculateShiftHours = (shift) => {
    return (shift.end_time - shift.start_time) / (1000 * 60 * 60);
  };

  const getPendingApplicationsCount = () => {
    return applications.filter(app => app.status === 'pending').length;
  };

  return (
    <Box p={4}>
      <VStack spacing={8} align="stretch">
        {/* Welcome Section */}
        <Flex 
          direction={{ base: 'column', md: 'row' }} 
          justify="space-between" 
          align={{ base: 'flex-start', md: 'center' }}
          bg={cardBg}
          p={6}
          borderRadius="lg"
          borderWidth="1px"
          borderColor={borderColor}
          shadow="md"
        >
          <HStack spacing={4}>
            <Avatar size="lg" name={user?.facility?.facility_name || 'Facility'} />
            <Box>
              <Heading size="lg">Welcome, {user?.facility?.facility_name}!</Heading>
              <Text color="gray.600">{user?.facility?.facility_type}</Text>
            </Box>
          </HStack>
          <HStack spacing={4} mt={{ base: 4, md: 0 }}>
            <Button colorScheme="green" onClick={() => navigate('/shifts/create')}>
              Post New Shift
            </Button>
            <Button colorScheme="blue" onClick={() => navigate('/facility/profile')}>
              Facility Profile
            </Button>
          </HStack>
        </Flex>

        {/* Stats Section */}
        <SimpleGrid columns={{ base: 1, md: 3 }} spacing={6}>
          <Card bg={statBg} borderRadius="lg" shadow="md">
            <CardBody>
              <Stat>
                <StatLabel fontSize="lg">Active Shifts</StatLabel>
                <StatNumber fontSize="3xl">{activeShifts.filter(s => s.status === 'open' || s.status === 'filled').length}</StatNumber>
                <StatHelpText>{activeShifts.filter(s => s.status === 'open').length} open positions</StatHelpText>
              </Stat>
            </CardBody>
          </Card>
          
          <Card bg={statBg} borderRadius="lg" shadow="md">
            <CardBody>
              <Stat>
                <StatLabel fontSize="lg">Pending Applications</StatLabel>
                <StatNumber fontSize="3xl">{getPendingApplicationsCount()}</StatNumber>
                <StatHelpText>Awaiting review</StatHelpText>
              </Stat>
            </CardBody>
          </Card>
          
          <Card bg={statBg} borderRadius="lg" shadow="md">
            <CardBody>
              <Stat>
                <StatLabel fontSize="lg">Total Spent</StatLabel>
                <StatNumber fontSize="3xl">${calculateTotalSpent()}</StatNumber>
                <StatHelpText>Last 30 days</StatHelpText>
              </Stat>
            </CardBody>
          </Card>
        </SimpleGrid>

        {/* Main Content */}
        <Grid templateColumns={{ base: '1fr', lg: '2fr 1fr' }} gap={6}>
          {/* Left Column */}
          <GridItem>
            <VStack spacing={6} align="stretch">
              {/* Active Shifts */}
              <Card bg={cardBg} borderWidth="1px" borderColor={borderColor} borderRadius="lg" shadow="md">
                <CardHeader>
                  <Flex justify="space-between" align="center">
                    <HStack>
                      <Icon as={FaCalendarAlt} color="green.500" />
                      <Heading size="md">Active Shifts</Heading>
                    </HStack>
                    <Button size="sm" colorScheme="green" variant="outline" onClick={() => navigate('/shifts/manage')}>
                      Manage All
                    </Button>
                  </Flex>
                </CardHeader>
                <CardBody>
                  {isLoading ? (
                    <Text>Loading shifts...</Text>
                  ) : activeShifts.length > 0 ? (
                    <Table variant="simple" size="sm">
                      <Thead>
                        <Tr>
                          <Th>Shift</Th>
                          <Th>Date & Time</Th>
                          <Th>Rate</Th>
                          <Th>Status</Th>
                          <Th>Applications</Th>
                          <Th></Th>
                        </Tr>
                      </Thead>
                      <Tbody>
                        {activeShifts.map(shift => (
                          <Tr key={shift.id}>
                            <Td>
                              <VStack align="start" spacing={0}>
                                <Text fontWeight="bold">{shift.title}</Text>
                                <Text fontSize="xs">{shift.required_role}</Text>
                              </VStack>
                            </Td>
                            <Td>
                              <Text fontSize="sm">{format(shift.start_time, 'MMM d, yyyy')}</Text>
                              <Text fontSize="xs">{format(shift.start_time, 'h:mm a')} - {format(shift.end_time, 'h:mm a')}</Text>
                            </Td>
                            <Td>${shift.hourly_rate}/hr</Td>
                            <Td>
                              <Badge colorScheme={getStatusColor(shift.status)}>
                                {shift.status}
                              </Badge>
                            </Td>
                            <Td>{shift.applications_count}</Td>
                            <Td>
                              <Button size="xs" colorScheme="blue" onClick={() => navigate(`/shifts/${shift.id}`)}>
                                View
                              </Button>
                            </Td>
                          </Tr>
                        ))}
                      </Tbody>
                    </Table>
                  ) : (
                    <Text>No active shifts. Post a new shift to find healthcare professionals.</Text>
                  )}
                </CardBody>
                <CardFooter pt={0}>
                  <Button colorScheme="green" w="100%" onClick={() => navigate('/shifts/create')}>
                    Post New Shift
                  </Button>
                </CardFooter>
              </Card>

              {/* Recent Applications */}
              <Card bg={cardBg} borderWidth="1px" borderColor={borderColor} borderRadius="lg" shadow="md">
                <CardHeader>
                  <Flex justify="space-between" align="center">
                    <HStack>
                      <Icon as={FaUserNurse} color="blue.500" />
                      <Heading size="md">Recent Applications</Heading>
                    </HStack>
                    <Button size="sm" colorScheme="blue" variant="outline" onClick={() => navigate('/applications')}>
                      View All
                    </Button>
                  </Flex>
                </CardHeader>
                <CardBody>
                  {isLoading ? (
                    <Text>Loading applications...</Text>
                  ) : applications.length > 0 ? (
                    <VStack spacing={4} align="stretch">
                      {applications.map(app => (
                        <Card key={app.id} variant="outline">
                          <CardBody>
                            <Flex justify="space-between" align="center" wrap="wrap">
                              <Box>
                                <Heading size="sm">{app.professional_name}</Heading>
                                <Text fontSize="sm">
                                  {app.professional_type} • {app.years_experience} years experience
                                </Text>
                                <Text fontSize="xs" color="gray.600">
                                  Applied for: {app.shift_title}
                                </Text>
                                <Text fontSize="xs" color="gray.600">
                                  Applied on: {format(app.application_date, 'MMM d, yyyy')}
                                </Text>
                              </Box>
                              <VStack align="flex-end">
                                <Badge colorScheme={getStatusColor(app.status)}>{app.status}</Badge>
                                <HStack spacing={2} mt={2}>
                                  <Button size="xs" colorScheme="green" onClick={() => navigate(`/applications/${app.id}/accept`)}>
                                    Accept
                                  </Button>
                                  <Button size="xs" colorScheme="red" variant="outline" onClick={() => navigate(`/applications/${app.id}/reject`)}>
                                    Reject
                                  </Button>
                                </HStack>
                              </VStack>
                            </Flex>
                          </CardBody>
                        </Card>
                      ))}
                    </VStack>
                  ) : (
                    <Text>No pending applications.</Text>
                  )}
                </CardBody>
              </Card>
            </VStack>
          </GridItem>

          {/* Right Column */}
          <GridItem>
            <VStack spacing={6} align="stretch">
              {/* Recent Payments */}
              <Card bg={cardBg} borderWidth="1px" borderColor={borderColor} borderRadius="lg" shadow="md">
                <CardHeader>
                  <Flex justify="space-between" align="center">
                    <HStack>
                      <Icon as={FaMoneyBillWave} color="green.500" />
                      <Heading size="md">Recent Payments</Heading>
                    </HStack>
                    <Button size="sm" colorScheme="green" variant="outline" onClick={() => navigate('/payments')}>
                      View All
                    </Button>
                  </Flex>
                </CardHeader>
                <CardBody>
                  {isLoading ? (
                    <Text>Loading payments...</Text>
                  ) : recentPayments.length > 0 ? (
                    <VStack spacing={4} align="stretch">
                      {recentPayments.map(payment => (
                        <Flex key={payment.id} justify="space-between" align="center" p={3} borderWidth="1px" borderRadius="md">
                          <Box>
                            <Text fontWeight="bold">${payment.amount.toFixed(2)}</Text>
                            <Text fontSize="sm">{payment.professional}</Text>
                            <Text fontSize="xs" color="gray.600">{payment.shift}</Text>
                            <Text fontSize="xs" color="gray.600">{format(payment.date, 'MMM d, yyyy')}</Text>
                          </Box>
                          <Badge colorScheme={payment.status === 'completed' ? 'green' : 'yellow'}>
                            {payment.status}
                          </Badge>
                        </Flex>
                      ))}
                    </VStack>
                  ) : (
                    <Text>No recent payments.</Text>
                  )}
                </CardBody>
              <
(Content truncated due to size limit. Use line ranges to read in chunks)