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
  Radio,
  RadioGroup,
  Stack,
  Checkbox,
  NumberInput,
  NumberInputField,
  NumberInputStepper,
  NumberIncrementStepper,
  NumberDecrementStepper,
  Icon
} from '@chakra-ui/react';
import { useNavigate } from 'react-router-dom';
import { FaCalendarAlt, FaDollarSign, FaClock, FaUserNurse } from 'react-icons/fa';

const CreateShift = () => {
  const navigate = useNavigate();
  const toast = useToast();
  const [isLoading, setIsLoading] = useState(false);
  
  const cardBg = useColorModeValue('white', 'gray.700');
  const borderColor = useColorModeValue('gray.200', 'gray.600');
  
  const [formData, setFormData] = useState({
    title: '',
    description: '',
    required_role: '',
    start_date: '',
    start_time: '',
    end_date: '',
    end_time: '',
    hourly_rate: '',
    is_urgent: false,
    required_skills: [],
    min_experience_years: 0,
    max_applicants: 10
  });

  const roleOptions = [
    { value: 'RN', label: 'Registered Nurse (RN)' },
    { value: 'LPN', label: 'Licensed Practical Nurse (LPN)' },
    { value: 'LVN', label: 'Licensed Vocational Nurse (LVN)' },
    { value: 'CNA', label: 'Certified Nursing Assistant (CNA)' },
    { value: 'STNA', label: 'State Tested Nursing Assistant (STNA)' },
    { value: 'CMA', label: 'Certified Medical Assistant (CMA)' },
    { value: 'Med-Tech', label: 'Med Tech' },
    { value: 'OR Tech', label: 'OR Tech' },
    { value: 'Rad Tech', label: 'Rad Tech' },
    { value: 'ER RN', label: 'ER RN' },
    { value: 'ICU/NICU RN', label: 'ICU/NICU RN' },
    { value: 'OR RN', label: 'OR RN' },
    { value: 'PREOP/PACU RN', label: 'PREOP/PACU RN' },
    { value: 'L&D RN', label: 'L&D RN' }
  ];

  const skillOptions = [
    { id: 'vital_signs', name: 'Vital Signs Monitoring' },
    { id: 'medication_admin', name: 'Medication Administration' },
    { id: 'iv_therapy', name: 'IV Therapy' },
    { id: 'wound_care', name: 'Wound Care' },
    { id: 'vent_management', name: 'Ventilator Management' },
    { id: 'ekg', name: 'EKG/ECG' },
    { id: 'phlebotomy', name: 'Phlebotomy' },
    { id: 'charting', name: 'Electronic Charting' },
    { id: 'telemetry', name: 'Telemetry' },
    { id: 'code_management', name: 'Code Management' }
  ];

  const handleChange = (e) => {
    const { name, value, type, checked } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: type === 'checkbox' ? checked : value
    }));
  };

  const handleSkillChange = (skillId) => {
    setFormData(prev => {
      const currentSkills = [...prev.required_skills];
      if (currentSkills.includes(skillId)) {
        return {
          ...prev,
          required_skills: currentSkills.filter(id => id !== skillId)
        };
      } else {
        return {
          ...prev,
          required_skills: [...currentSkills, skillId]
        };
      }
    });
  };

  const handleNumberChange = (name, value) => {
    setFormData(prev => ({
      ...prev,
      [name]: value
    }));
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    setIsLoading(true);
    
    // Combine date and time fields
    const startDateTime = new Date(`${formData.start_date}T${formData.start_time}`);
    const endDateTime = new Date(`${formData.end_date}T${formData.end_time}`);
    
    // Validate dates
    if (endDateTime <= startDateTime) {
      toast({
        title: 'Invalid Time Range',
        description: 'End time must be after start time.',
        status: 'error',
        duration: 5000,
        isClosable: true,
      });
      setIsLoading(false);
      return;
    }
    
    // In a real implementation, this would be an API call to create the shift
    setTimeout(() => {
      toast({
        title: 'Shift Created',
        description: 'Your shift has been successfully posted.',
        status: 'success',
        duration: 5000,
        isClosable: true,
      });
      setIsLoading(false);
      navigate('/shifts/manage');
    }, 1500);
  };

  return (
    <Box p={4}>
      <VStack spacing={8} align="stretch">
        <Heading size="lg">Create New Shift</Heading>
        <Text>Post a new shift to find qualified healthcare professionals.</Text>
        
        <Card bg={cardBg} borderWidth="1px" borderColor={borderColor} borderRadius="lg" shadow="md">
          <CardHeader>
            <Heading size="md">Shift Details</Heading>
          </CardHeader>
          
          <CardBody>
            <form onSubmit={handleSubmit}>
              <VStack spacing={6} align="stretch">
                <FormControl isRequired>
                  <FormLabel>Shift Title</FormLabel>
                  <Input
                    name="title"
                    value={formData.title}
                    onChange={handleChange}
                    placeholder="e.g., ICU Night Shift, ER Weekend Coverage"
                  />
                </FormControl>
                
                <FormControl>
                  <FormLabel>Description</FormLabel>
                  <Textarea
                    name="description"
                    value={formData.description}
                    onChange={handleChange}
                    placeholder="Provide details about the shift, responsibilities, and any special requirements"
                    rows={4}
                  />
                </FormControl>
                
                <FormControl isRequired>
                  <FormLabel>Required Role</FormLabel>
                  <Select
                    name="required_role"
                    value={formData.required_role}
                    onChange={handleChange}
                    placeholder="Select required healthcare role"
                  >
                    {roleOptions.map(role => (
                      <option key={role.value} value={role.value}>{role.label}</option>
                    ))}
                  </Select>
                </FormControl>
                
                <HStack spacing={4} align="flex-start">
                  <FormControl isRequired>
                    <FormLabel>Start Date</FormLabel>
                    <Input
                      type="date"
                      name="start_date"
                      value={formData.start_date}
                      onChange={handleChange}
                      min={new Date().toISOString().split('T')[0]}
                    />
                  </FormControl>
                  
                  <FormControl isRequired>
                    <FormLabel>Start Time</FormLabel>
                    <Input
                      type="time"
                      name="start_time"
                      value={formData.start_time}
                      onChange={handleChange}
                    />
                  </FormControl>
                </HStack>
                
                <HStack spacing={4} align="flex-start">
                  <FormControl isRequired>
                    <FormLabel>End Date</FormLabel>
                    <Input
                      type="date"
                      name="end_date"
                      value={formData.end_date}
                      onChange={handleChange}
                      min={formData.start_date || new Date().toISOString().split('T')[0]}
                    />
                  </FormControl>
                  
                  <FormControl isRequired>
                    <FormLabel>End Time</FormLabel>
                    <Input
                      type="time"
                      name="end_time"
                      value={formData.end_time}
                      onChange={handleChange}
                    />
                  </FormControl>
                </HStack>
                
                <FormControl isRequired>
                  <FormLabel>Hourly Rate ($)</FormLabel>
                  <NumberInput
                    min={0}
                    precision={2}
                    step={0.5}
                    value={formData.hourly_rate}
                    onChange={(value) => handleNumberChange('hourly_rate', value)}
                  >
                    <NumberInputField placeholder="Enter hourly rate" />
                    <NumberInputStepper>
                      <NumberIncrementStepper />
                      <NumberDecrementStepper />
                    </NumberInputStepper>
                  </NumberInput>
                </FormControl>
                
                <FormControl>
                  <Checkbox
                    name="is_urgent"
                    isChecked={formData.is_urgent}
                    onChange={handleChange}
                    colorScheme="red"
                  >
                    Mark as Urgent
                  </Checkbox>
                </FormControl>
                
                <Divider />
                
                <Heading size="sm">Additional Requirements</Heading>
                
                <FormControl>
                  <FormLabel>Required Skills</FormLabel>
                  <VStack align="start" spacing={2}>
                    {skillOptions.map(skill => (
                      <Checkbox
                        key={skill.id}
                        isChecked={formData.required_skills.includes(skill.id)}
                        onChange={() => handleSkillChange(skill.id)}
                      >
                        {skill.name}
                      </Checkbox>
                    ))}
                  </VStack>
                </FormControl>
                
                <HStack spacing={4} align="flex-start">
                  <FormControl>
                    <FormLabel>Minimum Years of Experience</FormLabel>
                    <NumberInput
                      min={0}
                      max={20}
                      value={formData.min_experience_years}
                      onChange={(value) => handleNumberChange('min_experience_years', value)}
                    >
                      <NumberInputField />
                      <NumberInputStepper>
                        <NumberIncrementStepper />
                        <NumberDecrementStepper />
                      </NumberInputStepper>
                    </NumberInput>
                  </FormControl>
                  
                  <FormControl>
                    <FormLabel>Maximum Applicants</FormLabel>
                    <NumberInput
                      min={1}
                      max={50}
                      value={formData.max_applicants}
                      onChange={(value) => handleNumberChange('max_applicants', value)}
                    >
                      <NumberInputField />
                      <NumberInputStepper>
                        <NumberIncrementStepper />
                        <NumberDecrementStepper />
                      </NumberInputStepper>
                    </NumberInput>
                  </FormControl>
                </HStack>
              </VStack>
            </form>
          </CardBody>
          
          <CardFooter>
            <HStack spacing={4} width="100%">
              <Button 
                variant="outline" 
                onClick={() => navigate('/shifts/manage')}
                flex={1}
              >
                Cancel
              </Button>
              <Button 
                colorScheme="green" 
                onClick={handleSubmit}
                isLoading={isLoading}
                flex={2}
              >
                Post Shift
              </Button>
            </HStack>
          </CardFooter>
        </Card>
        
        {/* Preview Card */}
        <Card bg={cardBg} borderWidth="1px" borderColor={borderColor} borderRadius="lg" shadow="md">
          <CardHeader>
            <Heading size="md">Shift Preview</Heading>
          </CardHeader>
          <CardBody>
            <VStack spacing={4} align="stretch">
              <Heading size="md">{formData.title || 'Shift Title'}</Heading>
              
              <HStack>
                <Icon as={FaUserNurse} color="blue.500" />
                <Text fontWeight="bold">{formData.required_role ? roleOptions.find(r => r.value === formData.required_role)?.label : 'Role'}</Text>
              </HStack>
              
              <HStack>
                <Icon as={FaCalendarAlt} color="green.500" />
                <Text>
                  {formData.start_date ? new Date(formData.start_date).toLocaleDateString() : 'Start Date'} 
                  {formData.start_time ? ' at ' + formData.start_time : ''}
                </Text>
              </HStack>
              
              <HStack>
                <Icon as={FaClock} color="purple.500" />
                <Text>
                  {formData.end_date ? new Date(formData.end_date).toLocaleDateString() : 'End Date'} 
                  {formData.end_time ? ' at ' + formData.end_time : ''}
                </Text>
              </HStack>
              
              <HStack>
                <Icon as={FaDollarSign} color="yellow.500" />
                <Text fontWeight="bold">${formData.hourly_rate || '0.00'}/hour</Text>
              </HStack>
              
              {formData.is_urgent && (
                <Text color="red.500" fontWeight="bold">URGENT</Text>
              )}
              
              {formData.description && (
                <Box>
                  <Text fontWeight="bold">Description:</Text>
                  <Text>{formData.description}</Text>
                </Box>
              )}
              
              {formData.required_skills.length > 0 && (
                <Box>
                  <Text fontWeight="bold">Required Skills:</Text>
                  <Text>
                    {formData.required_skills.map(skillId => 
                      skillOptions.find(s => s.id === skillId)?.name
                    ).join(', ')}
                  </Text>
                </Box>
              )}
              
              <Text>Minimum Experience: {formData.min_experience_years} years</Text>
            </VStack>
          </CardBody>
        </Card>
      </VStack>
    </Box>
  );
};

export default CreateShift;
