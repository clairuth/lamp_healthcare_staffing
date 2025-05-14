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
  SimpleGrid,
  Progress,
  Tabs,
  TabList,
  TabPanels,
  Tab,
  TabPanel
} from '@chakra-ui/react';
import { useNavigate } from 'react-router-dom';
import { FaClipboardCheck, FaCheckCircle, FaTimesCircle, FaExclamationCircle, FaPlus, FaEdit, FaTrash, FaChartLine } from 'react-icons/fa';
import { format } from 'date-fns';

const SkillsManagement = () => {
  const navigate = useNavigate();
  const toast = useToast();
  const { isOpen, onOpen, onClose } = useDisclosure();
  const [isLoading, setIsLoading] = useState(true);
  const [skills, setSkills] = useState([]);
  const [assessments, setAssessments] = useState([]);
  const [selectedSkill, setSelectedSkill] = useState(null);
  const [selectedAssessment, setSelectedAssessment] = useState(null);
  const [isEditing, setIsEditing] = useState(false);
  const [isSaving, setIsSaving] = useState(false);
  const [activeTab, setActiveTab] = useState(0);
  
  const cardBg = useColorModeValue('white', 'gray.700');
  const borderColor = useColorModeValue('gray.200', 'gray.600');
  
  const [formData, setFormData] = useState({
    name: '',
    description: '',
    category: '',
    required_for_roles: [],
    assessment_type: 'quiz',
    passing_score: 70
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

  const categoryOptions = [
    { value: 'clinical', label: 'Clinical Skills' },
    { value: 'technical', label: 'Technical Skills' },
    { value: 'specialty', label: 'Specialty Knowledge' },
    { value: 'certification', label: 'Certification' },
    { value: 'soft_skills', label: 'Soft Skills' }
  ];

  const assessmentTypeOptions = [
    { value: 'quiz', label: 'Multiple Choice Quiz' },
    { value: 'checklist', label: 'Skills Checklist' },
    { value: 'document', label: 'Document Upload' },
    { value: 'reference', label: 'Reference Check' }
  ];

  useEffect(() => {
    // In a real implementation, this would be an API call
    // Simulate fetching skills and assessments
    setTimeout(() => {
      setSkills([
        {
          id: '1',
          name: 'Medication Administration',
          description: 'Safe administration of medications including oral, injectable, and IV medications.',
          category: 'clinical',
          required_for_roles: ['RN', 'LPN', 'LVN'],
          assessment_type: 'quiz',
          passing_score: 80,
          created_at: new Date(2025, 2, 15),
          status: 'active'
        },
        {
          id: '2',
          name: 'Vital Signs Monitoring',
          description: 'Accurate measurement and recording of vital signs including blood pressure, pulse, respiration, and temperature.',
          category: 'clinical',
          required_for_roles: ['RN', 'LPN', 'LVN', 'CNA', 'STNA', 'CMA'],
          assessment_type: 'checklist',
          passing_score: 70,
          created_at: new Date(2025, 2, 20),
          status: 'active'
        },
        {
          id: '3',
          name: 'IV Therapy',
          description: 'Initiation and maintenance of intravenous therapy, including central lines.',
          category: 'clinical',
          required_for_roles: ['RN'],
          assessment_type: 'quiz',
          passing_score: 85,
          created_at: new Date(2025, 3, 5),
          status: 'active'
        },
        {
          id: '4',
          name: 'Wound Care',
          description: 'Assessment and treatment of various wound types, including dressing changes and infection prevention.',
          category: 'clinical',
          required_for_roles: ['RN', 'LPN', 'LVN'],
          assessment_type: 'quiz',
          passing_score: 75,
          created_at: new Date(2025, 3, 10),
          status: 'active'
        },
        {
          id: '5',
          name: 'Electronic Health Records',
          description: 'Proficiency in using electronic health record systems for documentation.',
          category: 'technical',
          required_for_roles: ['RN', 'LPN', 'LVN', 'CNA', 'STNA', 'CMA'],
          assessment_type: 'checklist',
          passing_score: 70,
          created_at: new Date(2025, 3, 15),
          status: 'active'
        }
      ]);
      
      setAssessments([
        {
          id: '1',
          skill_id: '1',
          skill_name: 'Medication Administration',
          title: 'Medication Administration Assessment',
          description: 'Test your knowledge of safe medication administration practices.',
          type: 'quiz',
          questions_count: 10,
          passing_score: 80,
          average_score: 85.2,
          completion_count: 128,
          created_at: new Date(2025, 2, 15),
          status: 'active'
        },
        {
          id: '2',
          skill_id: '2',
          skill_name: 'Vital Signs Monitoring',
          title: 'Vital Signs Skills Checklist',
          description: 'Demonstrate your ability to accurately measure and record vital signs.',
          type: 'checklist',
          questions_count: 8,
          passing_score: 70,
          average_score: 92.5,
          completion_count: 156,
          created_at: new Date(2025, 2, 20),
          status: 'active'
        },
        {
          id: '3',
          skill_id: '3',
          skill_name: 'IV Therapy',
          title: 'IV Therapy Knowledge Assessment',
          description: 'Test your knowledge of IV therapy principles and practices.',
          type: 'quiz',
          questions_count: 15,
          passing_score: 85,
          average_score: 79.8,
          completion_count: 94,
          created_at: new Date(2025, 3, 5),
          status: 'active'
        },
        {
          id: '4',
          skill_id: '4',
          skill_name: 'Wound Care',
          title: 'Wound Care Assessment',
          description: 'Test your knowledge of wound assessment and treatment.',
          type: 'quiz',
          questions_count: 12,
          passing_score: 75,
          average_score: 82.1,
          completion_count: 87,
          created_at: new Date(2025, 3, 10),
          status: 'active'
        },
        {
          id: '5',
          skill_id: '5',
          skill_name: 'Electronic Health Records',
          title: 'EHR Proficiency Checklist',
          description: 'Demonstrate your ability to use electronic health record systems.',
          type: 'checklist',
          questions_count: 10,
          passing_score: 70,
          average_score: 88.7,
          completion_count: 142,
          created_at: new Date(2025, 3, 15),
          status: 'active'
        }
      ]);
      
      setIsLoading(false);
    }, 1000);
  }, []);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: value
    }));
  };

  const handleRoleToggle = (role) => {
    setFormData(prev => {
      const currentRoles = [...prev.required_for_roles];
      if (currentRoles.includes(role)) {
        return {
          ...prev,
          required_for_roles: currentRoles.filter(r => r !== role)
        };
      } else {
        return {
          ...prev,
          required_for_roles: [...currentRoles, role]
        };
      }
    });
  };

  const handleAddSkill = () => {
    setSelectedSkill(null);
    setIsEditing(false);
    setFormData({
      name: '',
      description: '',
      category: '',
      required_for_roles: [],
      assessment_type: 'quiz',
      passing_score: 70
    });
    onOpen();
  };

  const handleEditSkill = (skill) => {
    setSelectedSkill(skill);
    setIsEditing(true);
    setFormData({
      name: skill.name,
      description: skill.description,
      category: skill.category,
      required_for_roles: skill.required_for_roles,
      assessment_type: skill.assessment_type,
      passing_score: skill.passing_score
    });
    onOpen();
  };

  const handleViewAssessment = (assessment) => {
    setSelectedAssessment(assessment);
    navigate(`/skills/assessments/${assessment.id}`);
  };

  const handleSubmit = () => {
    setIsSaving(true);
    
    // In a real implementation, this would be an API call to save the skill
    setTimeout(() => {
      if (isEditing && selectedSkill) {
        // Update existing skill
        const updatedSkills = skills.map(skill => 
          skill.id === selectedSkill.id 
            ? { 
                ...skill, 
                name: formData.name,
                description: formData.description,
                category: formData.category,
                required_for_roles: formData.required_for_roles,
                assessment_type: formData.assessment_type,
                passing_score: formData.passing_score
              } 
            : skill
        );
        setSkills(updatedSkills);
        
        toast({
          title: 'Skill Updated',
          description: `${formData.name} has been updated successfully.`,
          status: 'success',
          duration: 5000,
          isClosable: true,
        });
      } else {
        // Add new skill
        const newSkill = {
          id: (skills.length + 1).toString(),
          name: formData.name,
          description: formData.description,
          category: formData.category,
          required_for_roles: formData.required_for_roles,
          assessment_type: formData.assessment_type,
          passing_score: formData.passing_score,
          created_at: new Date(),
          status: 'active'
        };
        
        setSkills([...skills, newSkill]);
        
        toast({
          title: 'Skill Added',
          description: `${formData.name} has been added successfully.`,
          status: 'success',
          duration: 5000,
          isClosable: true,
        });
      }
      
      setIsSaving(false);
      onClose();
    }, 1500);
  };

  const handleDeleteSkill = (skillId) => {
    // In a real implementation, this would be an API call to delete the skill
    const updatedSkills = skills.filter(skill => skill.id !== skillId);
    setSkills(updatedSkills);
    
    toast({
      title: 'Skill Deleted',
      description: 'The skill has been deleted successfully.',
      status: 'success',
      duration: 5000,
      isClosable: true,
    });
  };

  const getCategoryLabel = (categoryValue) => {
    const category = categoryOptions.find(cat => cat.value === categoryValue);
    return category ? category.label : categoryValue;
  };

  const getAssessmentTypeLabel = (typeValue) => {
    const type = assessmentTypeOptions.find(t => t.value === typeValue);
    return type ? type.label : typeValue;
  };

  const getCategoryColor = (category) => {
    switch (category) {
      case 'clinical':
        return 'blue';
      case 'technical':
        return 'purple';
      case 'specialty':
        return 'orange';
      case 'certification':
        return 'green';
      case 'soft_skills':
        return 'pink';
      default:
        return 'gray';
    }
  };

  const getAssessmentTypeColor = (type) => {
    switch (type) {
      case 'quiz':
        return 'blue';
      case 'checklist':
        return 'green';
      case 'document':
        return 'orange';
      case 'reference':
        return 'purple';
      default:
        return 'gray';
    }
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
            <Heading size="lg">Skills Management</Heading>
            <Text color="gray.600">Manage healthcare skills and assessments</Text>
          </Box>
          
          <Button 
            colorScheme="blue" 
            onClick={handleAddSkill}
            leftIcon={<Icon as={FaPlus} />}
            mt={{ base: 4, md: 0 }}
          >
            Add New Skill
          </Button>
        </Flex>
        
        <Tabs variant="enclosed" index={activeTab} onChange={setActiveTab}>
          <TabList>
            <Tab>Skills ({skills.length})</Tab>
            <Tab>Assessments ({assessments.length})</Tab>
            <Tab>Analytics</Tab>
          </TabList>
          
          <TabPanels>
            {/* Skills Tab */}
            <TabPanel p={0} pt={4}>
              {isLoading ? (
                <Flex justify="center" p={10}>
                  <Spinner size="xl" />
                </Flex>
              ) : skills.length > 0 ? (
                <Table variant="simple" size={{ base: "sm", md: "md" }}>
                  <Thead>
                    <Tr>
                      <Th>Skill Name</Th>
                      <Th>Category</Th>
                      <Th>Required For</Th>
                      <Th>Assessment Type</Th>
                      <Th>Passing Score</Th>
                      <Th>Actions</Th>
                    </Tr>
                  </Thead>
                  <Tbody>
                    {skills.map(skill => (
                      <Tr key={skill.id}>
                        <Td>
                          <Text fontWeight="bold">{skill.name}</Text>
                          <Text fontSize="xs" color="gray.500" noOfLines={2}>
                            {skill.description}
                          </Text>
                        </Td>
                        <Td>
                          <Badge colorScheme={getCategoryColor(skill.category)}>
                            {getCategoryLabel(skill.category)}
                          </Badge>
                        </Td>
                        <Td>
                          <Text fontSize="sm">
                            {skill.required_for_roles.length > 3 
                              ? `${skill.required_for_roles.slice(0, 3).join(', ')} +${skill.required_for_roles.length - 3} more`
                              : skill.required_for_roles.join(', ')}
                          </Text>
                        </Td>
                        <Td>
                          <Badge colorScheme={getAssessmentTypeColor(skill.assessment_type)}>
                            {getAssessmentTypeLabel(skill.assessment_type)}
                          </Badge>
                        </Td>
                        <Td>{skill.passing_score}%</Td>
                        <Td>
                          <HStack spacing={2}>
                            <Button 
                              size="sm" 
                              colorScheme="blue" 
                              variant="outline"
                              leftIcon={<Icon as={FaEdit} />}
                              onClick={() => handleEditSkill(skill)}
                            >
                              Edit
                            </Button>
                            <Button 
                              size="sm" 
                              colorScheme="red" 
         
(Content truncated due to size limit. Use line ranges to read in chunks)