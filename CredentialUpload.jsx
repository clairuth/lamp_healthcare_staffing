import React, { useState, useEffect } from 'react';
import { 
  Box, 
  VStack, 
  HStack, 
  Heading, 
  Text, 
  Button, 
  FormControl, 
  FormLabel, 
  Input, 
  Select, 
  Textarea,
  Card,
  CardHeader,
  CardBody,
  CardFooter,
  Divider,
  useToast,
  useColorModeValue,
  FormHelperText,
  InputGroup,
  InputRightElement,
  Icon
} from '@chakra-ui/react';
import { useNavigate } from 'react-router-dom';
import { FaUpload, FaCheck, FaExclamationTriangle } from 'react-icons/fa';

const CredentialUpload = () => {
  const navigate = useNavigate();
  const toast = useToast();
  const [isLoading, setIsLoading] = useState(false);
  const [credentialTypes, setCredentialTypes] = useState([]);
  const [selectedFile, setSelectedFile] = useState(null);
  const [verificationStatus, setVerificationStatus] = useState(null);
  
  const cardBg = useColorModeValue('white', 'gray.700');
  const borderColor = useColorModeValue('gray.200', 'gray.600');
  
  const [formData, setFormData] = useState({
    credential_type: '',
    credential_number: '',
    issuing_authority: '',
    issue_date: '',
    expiration_date: '',
    notes: '',
    file: null
  });

  useEffect(() => {
    // In a real implementation, this would be an API call
    // Simulate fetching credential types
    setCredentialTypes([
      { id: 'rn_license', name: 'RN License' },
      { id: 'lpn_license', name: 'LPN/LVN License' },
      { id: 'cna_certification', name: 'CNA Certification' },
      { id: 'bls_certification', name: 'BLS Certification' },
      { id: 'acls_certification', name: 'ACLS Certification' },
      { id: 'pals_certification', name: 'PALS Certification' },
      { id: 'covid19_vaccination', name: 'COVID-19 Vaccination' },
      { id: 'tb_test', name: 'TB Test' },
      { id: 'flu_vaccination', name: 'Flu Vaccination' },
      { id: 'background_check', name: 'Background Check' },
      { id: 'other', name: 'Other' }
    ]);
  }, []);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: value
    }));
    
    // Reset verification status when credential type or number changes
    if (name === 'credential_type' || name === 'credential_number') {
      setVerificationStatus(null);
    }
  };

  const handleFileChange = (e) => {
    if (e.target.files.length > 0) {
      const file = e.target.files[0];
      setSelectedFile(file);
      setFormData(prev => ({
        ...prev,
        file: file
      }));
    }
  };

  const handleVerify = () => {
    // In a real implementation, this would call the Texas Board of Nursing API
    // For demonstration, we'll simulate the verification process
    setIsLoading(true);
    
    setTimeout(() => {
      if (formData.credential_type === 'rn_license' && formData.credential_number) {
        // Simulate successful verification
        setVerificationStatus('verified');
        toast({
          title: 'Verification Successful',
          description: 'Your license has been verified with the Texas Board of Nursing.',
          status: 'success',
          duration: 5000,
          isClosable: true,
        });
      } else {
        // Simulate failed verification
        setVerificationStatus('failed');
        toast({
          title: 'Verification Failed',
          description: 'Unable to verify your license. Please check the information and try again.',
          status: 'error',
          duration: 5000,
          isClosable: true,
        });
      }
      setIsLoading(false);
    }, 2000);
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    setIsLoading(true);
    
    // In a real implementation, this would be an API call to upload the credential
    setTimeout(() => {
      toast({
        title: 'Credential Uploaded',
        description: 'Your credential has been successfully uploaded.',
        status: 'success',
        duration: 5000,
        isClosable: true,
      });
      setIsLoading(false);
      navigate('/credentials');
    }, 1500);
  };

  return (
    <Box p={4}>
      <VStack spacing={8} align="stretch">
        <Heading size="lg">Upload Credential</Heading>
        <Text>Upload your professional credentials and licenses to apply for shifts.</Text>
        
        <Card bg={cardBg} borderWidth="1px" borderColor={borderColor} borderRadius="lg" shadow="md">
          <CardHeader>
            <Heading size="md">Credential Information</Heading>
          </CardHeader>
          
          <CardBody>
            <form onSubmit={handleSubmit}>
              <VStack spacing={6} align="stretch">
                <FormControl isRequired>
                  <FormLabel>Credential Type</FormLabel>
                  <Select 
                    name="credential_type"
                    value={formData.credential_type}
                    onChange={handleChange}
                    placeholder="Select credential type"
                  >
                    {credentialTypes.map(type => (
                      <option key={type.id} value={type.id}>{type.name}</option>
                    ))}
                  </Select>
                </FormControl>
                
                <FormControl isRequired>
                  <FormLabel>Credential Number</FormLabel>
                  <InputGroup>
                    <Input
                      name="credential_number"
                      value={formData.credential_number}
                      onChange={handleChange}
                      placeholder="Enter license or certification number"
                    />
                    {verificationStatus && (
                      <InputRightElement>
                        <Icon 
                          as={verificationStatus === 'verified' ? FaCheck : FaExclamationTriangle} 
                          color={verificationStatus === 'verified' ? 'green.500' : 'red.500'} 
                        />
                      </InputRightElement>
                    )}
                  </InputGroup>
                  {formData.credential_type === 'rn_license' && (
                    <FormHelperText>
                      For Texas RN licenses, enter your license number for automatic verification.
                    </FormHelperText>
                  )}
                </FormControl>
                
                {formData.credential_type === 'rn_license' && formData.credential_number && !verificationStatus && (
                  <Button 
                    colorScheme="blue" 
                    onClick={handleVerify}
                    isLoading={isLoading}
                    leftIcon={<FaCheck />}
                  >
                    Verify with Texas Board of Nursing
                  </Button>
                )}
                
                {verificationStatus === 'verified' && (
                  <Box p={3} bg="green.50" borderRadius="md" borderWidth="1px" borderColor="green.200">
                    <HStack>
                      <Icon as={FaCheck} color="green.500" />
                      <Text color="green.700">License verified with Texas Board of Nursing</Text>
                    </HStack>
                  </Box>
                )}
                
                {verificationStatus === 'failed' && (
                  <Box p={3} bg="red.50" borderRadius="md" borderWidth="1px" borderColor="red.200">
                    <HStack>
                      <Icon as={FaExclamationTriangle} color="red.500" />
                      <Text color="red.700">Verification failed. Please check your license number.</Text>
                    </HStack>
                  </Box>
                )}
                
                <Divider />
                
                <FormControl isRequired>
                  <FormLabel>Issuing Authority</FormLabel>
                  <Input
                    name="issuing_authority"
                    value={formData.issuing_authority}
                    onChange={handleChange}
                    placeholder="e.g., Texas Board of Nursing"
                  />
                </FormControl>
                
                <HStack spacing={4}>
                  <FormControl isRequired>
                    <FormLabel>Issue Date</FormLabel>
                    <Input
                      type="date"
                      name="issue_date"
                      value={formData.issue_date}
                      onChange={handleChange}
                    />
                  </FormControl>
                  
                  <FormControl>
                    <FormLabel>Expiration Date</FormLabel>
                    <Input
                      type="date"
                      name="expiration_date"
                      value={formData.expiration_date}
                      onChange={handleChange}
                    />
                    <FormHelperText>Leave blank if no expiration</FormHelperText>
                  </FormControl>
                </HStack>
                
                <FormControl isRequired>
                  <FormLabel>Upload Document</FormLabel>
                  <Box
                    borderWidth="1px"
                    borderRadius="md"
                    borderStyle="dashed"
                    p={6}
                    textAlign="center"
                    borderColor={selectedFile ? "green.300" : "gray.300"}
                    bg={selectedFile ? "green.50" : "gray.50"}
                    cursor="pointer"
                    onClick={() => document.getElementById('file-upload').click()}
                  >
                    <input
                      id="file-upload"
                      type="file"
                      accept=".pdf,.jpg,.jpeg,.png"
                      onChange={handleFileChange}
                      style={{ display: 'none' }}
                    />
                    <Icon as={FaUpload} w={8} h={8} color={selectedFile ? "green.500" : "gray.400"} mb={3} />
                    <Heading size="sm" mb={2}>
                      {selectedFile ? selectedFile.name : "Click to upload or drag and drop"}
                    </Heading>
                    <Text fontSize="sm" color="gray.500">
                      PDF, JPG or PNG (max. 5MB)
                    </Text>
                    {selectedFile && (
                      <Text color="green.500" mt={2}>
                        File selected
                      </Text>
                    )}
                  </Box>
                </FormControl>
                
                <FormControl>
                  <FormLabel>Additional Notes</FormLabel>
                  <Textarea
                    name="notes"
                    value={formData.notes}
                    onChange={handleChange}
                    placeholder="Any additional information about this credential"
                    rows={3}
                  />
                </FormControl>
              </VStack>
            </form>
          </CardBody>
          
          <CardFooter>
            <HStack spacing={4} width="100%">
              <Button 
                variant="outline" 
                onClick={() => navigate('/credentials')}
                flex={1}
              >
                Cancel
              </Button>
              <Button 
                colorScheme="blue" 
                onClick={handleSubmit}
                isLoading={isLoading}
                flex={2}
              >
                Upload Credential
              </Button>
            </HStack>
          </CardFooter>
        </Card>
      </VStack>
    </Box>
  );
};

export default CredentialUpload;
