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
  InputGroup, 
  InputRightElement, 
  IconButton,
  useColorModeValue
} from '@chakra-ui/react';
import { FaEye, FaEyeSlash } from 'react-icons/fa';

const Login = () => {
  const navigate = useNavigate();
  const { login } = useAuth();
  const [isLoading, setIsLoading] = useState(false);
  const [showPassword, setShowPassword] = useState(false);
  
  const [formData, setFormData] = useState({
    email: '',
    password: '',
    remember: false
  });

  const handleChange = (e) => {
    const { name, value, type, checked } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: type === 'checkbox' ? checked : value
    }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setIsLoading(true);

    try {
      await login(formData);
      toast.success('Login successful!');
      navigate('/dashboard');
    } catch (error) {
      console.error('Login error:', error);
      toast.error(error.response?.data?.message || 'Invalid credentials. Please try again.');
    } finally {
      setIsLoading(false);
    }
  };

  const cardBg = useColorModeValue('white', 'gray.700');
  const borderColor = useColorModeValue('gray.200', 'gray.600');

  return (
    <VStack spacing={8} mx="auto" maxW="md" py={12} px={6} w="100%">
      <Heading fontSize={'4xl'} textAlign={'center'}>
        Sign In
      </Heading>
      <Text fontSize={'lg'} color={'gray.600'} textAlign={'center'}>
        to access your account
      </Text>
      
      <Card bg={cardBg} borderWidth="1px" borderColor={borderColor} borderRadius="lg" w="100%">
        <CardHeader>
          <Heading size="md" textAlign="center">Healthcare Staffing Platform</Heading>
        </CardHeader>
        
        <CardBody>
          <form onSubmit={handleSubmit}>
            <VStack spacing={4}>
              <FormControl id="email" isRequired>
                <FormLabel>Email address</FormLabel>
                <Input
                  type="email"
                  name="email"
                  value={formData.email}
                  onChange={handleChange}
                />
              </FormControl>
              
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
              
              <FormControl display="flex" alignItems="center" justifyContent="space-between" w="100%">
                <label style={{ display: 'flex', alignItems: 'center' }}>
                  <input
                    type="checkbox"
                    name="remember"
                    checked={formData.remember}
                    onChange={handleChange}
                    style={{ marginRight: '8px' }}
                  />
                  Remember me
                </label>
                <Link to="/forgot-password" style={{ color: '#3182ce', fontSize: '0.875rem' }}>
                  Forgot password?
                </Link>
              </FormControl>
              
              <Button
                type="submit"
                colorScheme="blue"
                size="lg"
                w="100%"
                mt={4}
                isLoading={isLoading}
              >
                Sign In
              </Button>
            </VStack>
          </form>
          
          <Text mt={4} textAlign="center">
            Don't have an account?{' '}
            <Link to="/register" style={{ color: '#3182ce', fontWeight: 'bold' }}>
              Create Account
            </Link>
          </Text>
        </CardBody>
      </Card>
    </VStack>
  );
};

export default Login;
