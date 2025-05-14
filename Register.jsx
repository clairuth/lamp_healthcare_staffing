import React, { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { useAuth } from '../../contexts/AuthContext';
import { toast } from 'react-toastify';
import { 
  Card, 
  CardHeader, 
  CardBody, 
  FormControl, 
  FormLabel, 
  Input, 
  Button, 
  Heading, 
  Text, 
  VStack, 
  HStack, 
  Divider, 
  InputGroup, 
  InputRightElement, 
  IconButton,
  Select,
  useColorModeValue
} from '@chakra-ui/react';
import { FaEye, FaEyeSlash } from 'react-icons/fa';

const Register = () => {
  const navigate = useNavigate();
  const { register } = useAuth();
  const [isLoading, setIsLoading] = useState(false);
  const [showPassword, setShowPassword] = useState(false);
  const [userType, setUserType] = useState('professional');
  
  const [formData, setFormData] = useState({
    email: '',
    password: '',
    password_confirmation: '',
    first_name: '',
    last_name: '',
    phone_number: '',
    address: '',
    city: '',
    state: '',
    zip_code: '',
    user_type: 'professional',
    // Professional specific fields
    professional_type: 'RN',
    years_experience: '',
    // Facility specific fields
    facility_name: '',
    facility_type_id: '',
  });

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: value
    }));
  };

  const handleUserTypeChange = (e) => {
    const value = e.target.value;
    setUserType(value);
    setFormData(prev => ({
      ...prev,
      user_type: value
    }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setIsLoading(true);

    try {
      // Validate passwords match
      if (formData.password !== formData.password_confirmation) {
        toast.error('Passwords do not match');
        setIsLoading(false);
        return;
      }

      // Call register API
      await register(formData);
      toast.success('Registration successful!');
      navigate('/dashboard');
    } catch (error) {
      console.error('Registration error:', error);
      toast.error(error.response?.data?.message || 'Registration failed. Please try again.');
    } finally {
      setIsLoading(false);
    }
  };

  const cardBg = useColorModeValue('white', 'gray.700');
  const borderColor = useColorModeValue('gray.200', 'gray.600');

  return (
    <VStack spacing={8} mx="auto" maxW="lg" py={12} px={6} w="100%">
      <Heading fontSize={'4xl'} textAlign={'center'}>
        Create Your Account
      </Heading>
      <Text fontSize={'lg'} color={'gray.600'} textAlign={'center'}>
        Join our healthcare staffing platform ✨
      </Text>
      
      <Card bg={cardBg} borderWidth="1px" borderColor={borderColor} borderRadius="lg" w="100%">
        <CardHeader>
          <HStack spacing={4}>
            <Button 
              flex={1} 
              variant={userType === 'professional' ? 'solid' : 'outline'} 
              colorScheme="blue"
              onClick={() => handleUserTypeChange({ target: { value: 'professional' } })}
            >
              Healthcare Professional
            </Button>
            <Button 
              flex={1} 
              variant={userType === 'facility' ? 'solid' : 'outline'} 
              colorScheme="green"
              onClick={() => handleUserTypeChange({ target: { value: 'facility' } })}
            >
              Healthcare Facility
            </Button>
          </HStack>
        </CardHeader>
        
        <CardBody>
          <form onSubmit={handleSubmit}>
            <VStack spacing={4}>
              {/* Common Fields */}
              <FormControl id="email" isRequired>
                <FormLabel>Email address</FormLabel>
                <Input
                  type="email"
                  name="email"
                  value={formData.email}
                  onChange={handleChange}
                />
              </FormControl>
              
              <HStack w="100%">
                <FormControl id="first_name" isRequired>
                  <FormLabel>First Name</FormLabel>
                  <Input
                    type="text"
                    name="first_name"
                    value={formData.first_name}
                    onChange={handleChange}
                  />
                </FormControl>
                
                <FormControl id="last_name" isRequired>
                  <FormLabel>Last Name</FormLabel>
                  <Input
                    type="text"
                    name="last_name"
                    value={formData.last_name}
                    onChange={handleChange}
                  />
                </FormControl>
              </HStack>
              
              <FormControl id="phone_number" isRequired>
                <FormLabel>Phone Number</FormLabel>
                <Input
                  type="tel"
                  name="phone_number"
                  value={formData.phone_number}
                  onChange={handleChange}
                />
              </FormControl>
              
              <FormControl id="address">
                <FormLabel>Address</FormLabel>
                <Input
                  type="text"
                  name="address"
                  value={formData.address}
                  onChange={handleChange}
                />
              </FormControl>
              
              <HStack w="100%">
                <FormControl id="city">
                  <FormLabel>City</FormLabel>
                  <Input
                    type="text"
                    name="city"
                    value={formData.city}
                    onChange={handleChange}
                  />
                </FormControl>
                
                <FormControl id="state">
                  <FormLabel>State</FormLabel>
                  <Input
                    type="text"
                    name="state"
                    value={formData.state}
                    onChange={handleChange}
                  />
                </FormControl>
                
                <FormControl id="zip_code">
                  <FormLabel>ZIP Code</FormLabel>
                  <Input
                    type="text"
                    name="zip_code"
                    value={formData.zip_code}
                    onChange={handleChange}
                  />
                </FormControl>
              </HStack>
              
              {/* Professional Specific Fields */}
              {userType === 'professional' && (
                <>
                  <Divider />
                  <Heading size="md">Professional Information</Heading>
                  
                  <FormControl id="professional_type" isRequired>
                    <FormLabel>Professional Type</FormLabel>
                    <Select
                      name="professional_type"
                      value={formData.professional_type}
                      onChange={handleChange}
                    >
                      <option value="RN">Registered Nurse (RN)</option>
                      <option value="LPN">Licensed Practical Nurse (LPN)</option>
                      <option value="LVN">Licensed Vocational Nurse (LVN)</option>
                      <option value="CNA">Certified Nursing Assistant (CNA)</option>
                      <option value="STNA">State Tested Nursing Assistant (STNA)</option>
                      <option value="CMA">Certified Medical Assistant (CMA)</option>
                      <option value="Med-Tech">Med Tech</option>
                      <option value="OR Tech">OR Tech</option>
                      <option value="Rad Tech">Rad Tech</option>
                      <option value="ER RN">ER RN</option>
                      <option value="ICU/NICU RN">ICU/NICU RN</option>
                      <option value="OR RN">OR RN</option>
                      <option value="PREOP/PACU RN">PREOP/PACU RN</option>
                      <option value="L&D RN">L&D RN</option>
                    </Select>
                  </FormControl>
                  
                  <FormControl id="years_experience" isRequired>
                    <FormLabel>Years of Experience</FormLabel>
                    <Input
                      type="number"
                      name="years_experience"
                      value={formData.years_experience}
                      onChange={handleChange}
                      min="0"
                    />
                  </FormControl>
                </>
              )}
              
              {/* Facility Specific Fields */}
              {userType === 'facility' && (
                <>
                  <Divider />
                  <Heading size="md">Facility Information</Heading>
                  
                  <FormControl id="facility_name" isRequired>
                    <FormLabel>Facility Name</FormLabel>
                    <Input
                      type="text"
                      name="facility_name"
                      value={formData.facility_name}
                      onChange={handleChange}
                    />
                  </FormControl>
                  
                  <FormControl id="facility_type_id" isRequired>
                    <FormLabel>Facility Type</FormLabel>
                    <Select
                      name="facility_type_id"
                      value={formData.facility_type_id}
                      onChange={handleChange}
                    >
                      <option value="">Select Facility Type</option>
                      <option value="hospital">Hospital</option>
                      <option value="nursing_home">Nursing Home</option>
                      <option value="assisted_living">Assisted Living</option>
                      <option value="rehabilitation">Rehabilitation Center</option>
                      <option value="clinic">Clinic</option>
                      <option value="home_health">Home Health Agency</option>
                    </Select>
                  </FormControl>
                </>
              )}
              
              <Divider />
              
              <FormControl id="password" isRequired>
                <FormLabel>Password</FormLabel>
                <InputGroup>
                  <Input
                    type={showPassword ? 'text' : 'password'}
                    name="password"
                    value={formData.password}
                    onChange={handleChange}
                  />
                  <InputRightElement>
                    <IconButton
                      aria-label={showPassword ? 'Hide password' : 'Show password'}
                      icon={showPassword ? <FaEyeSlash /> : <FaEye />}
                      onClick={() => setShowPassword(!showPassword)}
                      variant="ghost"
                      size="sm"
                    />
                  </InputRightElement>
                </InputGroup>
              </FormControl>
              
              <FormControl id="password_confirmation" isRequired>
                <FormLabel>Confirm Password</FormLabel>
                <Input
                  type="password"
                  name="password_confirmation"
                  value={formData.password_confirmation}
                  onChange={handleChange}
                />
              </FormControl>
              
              <Button
                type="submit"
                colorScheme={userType === 'professional' ? 'blue' : 'green'}
                size="lg"
                w="100%"
                mt={4}
                isLoading={isLoading}
              >
                Create Account
              </Button>
            </VStack>
          </form>
          
          <Text mt={4} textAlign="center">
            Already have an account?{' '}
            <Link to="/login" style={{ color: '#3182ce', fontWeight: 'bold' }}>
              Sign In
            </Link>
          </Text>
        </CardBody>
      </Card>
    </VStack>
  );
};

export default Register;
