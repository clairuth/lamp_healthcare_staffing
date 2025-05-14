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
  Divider,
  useColorModeValue,
  Icon,
  SimpleGrid
} from '@chakra-ui/react';
import { useAuth } from '../../contexts/AuthContext';
import { useNavigate } from 'react-router-dom';
import { FaCalendarAlt, FaMoneyBillWave, FaUserNurse, FaHospital, FaClipboardCheck } from 'react-icons/fa';
import { format } from 'date-fns';

const ProfessionalDashboard = () => {
  const { user } = useAuth();
  const navigate = useNavigate();
  const [upcomingShifts, setUpcomingShifts] = useState([]);
  const [recentPayments, setRecentPayments] = useState([]);
  const [credentials, setCredentials] = useState([]);
  const [isLoading, setIsLoading] = useState(true);

  const cardBg = useColorModeValue('white', 'gray.700');
  const borderColor = useColorModeValue('gray.200', 'gray.600');
  const statBg = useColorModeValue('blue.50', 'blue.900');

  useEffect(() => {
    // In a real implementation, these would be API calls
    const fetchDashboardData = async () => {
      try {
        // Simulate API calls with timeout
        setTimeout(() => {
          // Mock data for demonstration
          setUpcomingShifts([
            {
              id: '1',
              title: 'ICU Night Shift',
              facility: 'Memorial Hospital',
              start_time: new Date(2025, 3, 22, 19, 0),
              end_time: new Date(2025, 3, 23, 7, 0),
              hourly_rate: 45.00,
              status: 'accepted'
            },
            {
              id: '2',
              title: 'ER Morning Shift',
              facility: 'City Medical Center',
              start_time: new Date(2025, 3, 25, 7, 0),
              end_time: new Date(2025, 3, 25, 15, 0),
              hourly_rate: 42.50,
              status: 'accepted'
            }
          ]);
          
          setRecentPayments([
            {
              id: '1',
              amount: 360.00,
              date: new Date(2025, 3, 15),
              facility: 'Memorial Hospital',
              status: 'completed'
            },
            {
              id: '2',
              amount: 297.50,
              date: new Date(2025, 3, 10),
              facility: 'City Medical Center',
              status: 'completed'
            }
          ]);
          
          setCredentials([
            {
              id: '1',
              credential_name: 'RN License',
              issuing_authority: 'Texas Board of Nursing',
              expiration_date: new Date(2026, 5, 30),
              verification_status: 'verified'
            },
            {
              id: '2',
              credential_name: 'BLS Certification',
              issuing_authority: 'American Heart Association',
              expiration_date: new Date(2025, 8, 15),
              verification_status: 'verified'
            },
            {
              id: '3',
              credential_name: 'COVID-19 Vaccination',
              issuing_authority: 'CDC',
              expiration_date: null,
              verification_status: 'verified'
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
      case 'verified':
        return 'green';
      case 'pending':
        return 'yellow';
      case 'rejected':
        return 'red';
      case 'expired':
        return 'gray';
      default:
        return 'blue';
    }
  };

  const calculateTotalEarnings = () => {
    return recentPayments.reduce((total, payment) => total + payment.amount, 0).toFixed(2);
  };

  const calculateShiftHours = (shift) => {
    return (shift.end_time - shift.start_time) / (1000 * 60 * 60);
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
            <Avatar size="lg" name={user?.first_name + ' ' + user?.last_name} />
            <Box>
              <Heading size="lg">Welcome, {user?.first_name}!</Heading>
              <Text color="gray.600">{user?.professional?.professional_type}</Text>
            </Box>
          </HStack>
          <HStack spacing={4} mt={{ base: 4, md: 0 }}>
            <Button colorScheme="blue" onClick={() => navigate('/shifts/available')}>
              Find Shifts
            </Button>
            <Button colorScheme="green" onClick={() => navigate('/profile')}>
              View Profile
            </Button>
          </HStack>
        </Flex>

        {/* Stats Section */}
        <SimpleGrid columns={{ base: 1, md: 3 }} spacing={6}>
          <Card bg={statBg} borderRadius="lg" shadow="md">
            <CardBody>
              <Stat>
                <StatLabel fontSize="lg">Total Earnings</StatLabel>
                <StatNumber fontSize="3xl">${calculateTotalEarnings()}</StatNumber>
                <StatHelpText>Last 30 days</StatHelpText>
              </Stat>
            </CardBody>
          </Card>
          
          <Card bg={statBg} borderRadius="lg" shadow="md">
            <CardBody>
              <Stat>
                <StatLabel fontSize="lg">Upcoming Shifts</StatLabel>
                <StatNumber fontSize="3xl">{upcomingShifts.length}</StatNumber>
                <StatHelpText>Next 7 days</StatHelpText>
              </Stat>
            </CardBody>
          </Card>
          
          <Card bg={statBg} borderRadius="lg" shadow="md">
            <CardBody>
              <Stat>
                <StatLabel fontSize="lg">Credentials</StatLabel>
                <StatNumber fontSize="3xl">{credentials.length}</StatNumber>
                <StatHelpText>
                  {credentials.filter(c => c.verification_status === 'verified').length} verified
                </StatHelpText>
              </Stat>
            </CardBody>
          </Card>
        </SimpleGrid>

        {/* Main Content */}
        <Grid templateColumns={{ base: '1fr', lg: '2fr 1fr' }} gap={6}>
          {/* Left Column */}
          <GridItem>
            <VStack spacing={6} align="stretch">
              {/* Upcoming Shifts */}
              <Card bg={cardBg} borderWidth="1px" borderColor={borderColor} borderRadius="lg" shadow="md">
                <CardHeader>
                  <Flex justify="space-between" align="center">
                    <HStack>
                      <Icon as={FaCalendarAlt} color="blue.500" />
                      <Heading size="md">Upcoming Shifts</Heading>
                    </HStack>
                    <Button size="sm" colorScheme="blue" variant="outline" onClick={() => navigate('/shifts/my-shifts')}>
                      View All
                    </Button>
                  </Flex>
                </CardHeader>
                <CardBody>
                  {isLoading ? (
                    <Text>Loading shifts...</Text>
                  ) : upcomingShifts.length > 0 ? (
                    <VStack spacing={4} align="stretch">
                      {upcomingShifts.map(shift => (
                        <Card key={shift.id} variant="outline">
                          <CardBody>
                            <Flex justify="space-between" align="center" wrap="wrap">
                              <Box>
                                <Heading size="sm">{shift.title}</Heading>
                                <Text>{shift.facility}</Text>
                                <Text fontSize="sm" color="gray.600">
                                  {format(shift.start_time, 'MMM d, yyyy h:mm a')} - {format(shift.end_time, 'h:mm a')}
                                </Text>
                              </Box>
                              <VStack align="flex-end">
                                <Badge colorScheme="green">${shift.hourly_rate}/hr</Badge>
                                <Text fontWeight="bold">${(shift.hourly_rate * calculateShiftHours(shift)).toFixed(2)} total</Text>
                              </VStack>
                            </Flex>
                          </CardBody>
                          <CardFooter pt={0}>
                            <Button size="sm" colorScheme="blue" w="100%" onClick={() => navigate(`/shifts/${shift.id}`)}>
                              View Details
                            </Button>
                          </CardFooter>
                        </Card>
                      ))}
                    </VStack>
                  ) : (
                    <Text>No upcoming shifts. Find new opportunities!</Text>
                  )}
                </CardBody>
                <CardFooter pt={0}>
                  <Button colorScheme="blue" w="100%" onClick={() => navigate('/shifts/available')}>
                    Find New Shifts
                  </Button>
                </CardFooter>
              </Card>

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
                            <Text fontSize="sm">{payment.facility}</Text>
                            <Text fontSize="sm" color="gray.600">{format(payment.date, 'MMM d, yyyy')}</Text>
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
              </Card>
            </VStack>
          </GridItem>

          {/* Right Column */}
          <GridItem>
            <VStack spacing={6} align="stretch">
              {/* Credentials */}
              <Card bg={cardBg} borderWidth="1px" borderColor={borderColor} borderRadius="lg" shadow="md">
                <CardHeader>
                  <Flex justify="space-between" align="center">
                    <HStack>
                      <Icon as={FaClipboardCheck} color="purple.500" />
                      <Heading size="md">My Credentials</Heading>
                    </HStack>
                    <Button size="sm" colorScheme="purple" variant="outline" onClick={() => navigate('/credentials')}>
                      View All
                    </Button>
                  </Flex>
                </CardHeader>
                <CardBody>
                  {isLoading ? (
                    <Text>Loading credentials...</Text>
                  ) : credentials.length > 0 ? (
                    <VStack spacing={4} align="stretch">
                      {credentials.map(credential => (
                        <Box key={credential.id} p={3} borderWidth="1px" borderRadius="md">
                          <Flex justify="space-between" align="center">
                            <Heading size="sm">{credential.credential_name}</Heading>
                            <Badge colorScheme={getStatusColor(credential.verification_status)}>
                              {credential.verification_status}
                            </Badge>
                          </Flex>
                          <Text fontSize="sm">{credential.issuing_authority}</Text>
                          {credential.expiration_date && (
                            <Text fontSize="sm" color={new Date() > credential.expiration_date ? "red.500" : "gray.600"}>
                              Expires: {format(credential.expiration_date, 'MMM d, yyyy')}
                            </Text>
                          )}
                        </Box>
                      ))}
                    </VStack>
                  ) : (
                    <Text>No credentials added yet.</Text>
                  )}
                </CardBody>
                <CardFooter pt={0}>
                  <Button colorScheme="purple" w="100%" onClick={() => navigate('/credentials/add')}>
                    Add New Credential
                  </Button>
                </CardFooter>
              </Card>

              {/* Quick Links */}
              <Card bg={cardBg} borderWidth="1px" borderColor={borderColor} borderRadius="lg" shadow="md">
                <CardHeader>
                  <Heading size="md">Quick Links</Heading>
                </CardHeader>
                <CardBody>
                  <VStack spacing={3} align="stretch">
                    <Button leftIcon={<FaUserNurse />} justifyContent="flex-start" variant="outline" onClick={() => navigate('/profile')}>
                      My Profile
                    </Button>
                    <Button leftIcon={<FaHospital />} justifyContent="flex-start" variant="outline" onClick={() => navigate('/facilities')}>
                      Browse Facilities
                    </Button>
                    <Button leftIcon={<FaClipboardCheck />} justifyContent="flex-start" variant="outline" onClick={() => navigate('/skills/assessments')}>
                      Skill Assessments
                    </Button>
                    <Button leftIcon={<FaMoneyBillWave />} justifyContent="flex-start" variant="outline" onClick={() => navigate('/payment-methods')}>
                      Payment Methods
                    </Button>
                  </VStack>
                </CardBody>
              </Card>
            </VStack>
          </GridItem>
        </Grid>
      </VStack>
    </Box>
  );
};

export default ProfessionalDashboard;
