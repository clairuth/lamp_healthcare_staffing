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
  SimpleGrid,
  Badge,
  Divider,
  useColorModeValue,
  Icon,
  Flex,
  Spinner,
  Select,
  FormControl,
  FormLabel
} from '@chakra-ui/react';
import { useNavigate } from 'react-router-dom';
import { FaClipboardCheck, FaCheckCircle, FaTimesCircle, FaExclamationCircle } from 'react-icons/fa';

const SkillAssessment = () => {
  const navigate = useNavigate();
  const [isLoading, setIsLoading] = useState(true);
  const [currentQuestion, setCurrentQuestion] = useState(0);
  const [selectedAnswers, setSelectedAnswers] = useState({});
  const [assessmentComplete, setAssessmentComplete] = useState(false);
  const [assessmentResult, setAssessmentResult] = useState(null);
  const [assessmentType, setAssessmentType] = useState('');
  const [questions, setQuestions] = useState([]);
  
  const cardBg = useColorModeValue('white', 'gray.700');
  const borderColor = useColorModeValue('gray.200', 'gray.600');
  const correctBg = useColorModeValue('green.50', 'green.900');
  const incorrectBg = useColorModeValue('red.50', 'red.900');

  // Available assessment types
  const assessmentTypes = [
    { id: 'rn_general', name: 'Registered Nurse - General Knowledge' },
    { id: 'rn_critical_care', name: 'Registered Nurse - Critical Care' },
    { id: 'lpn_general', name: 'LPN/LVN - General Knowledge' },
    { id: 'cna_general', name: 'CNA - General Knowledge' },
    { id: 'med_admin', name: 'Medication Administration' },
    { id: 'infection_control', name: 'Infection Control' }
  ];

  // Sample questions for RN General Knowledge assessment
  const rnGeneralQuestions = [
    {
      id: 1,
      question: "A patient is admitted with severe dehydration. Which of the following IV fluids would be most appropriate for initial volume replacement?",
      options: [
        { id: 'a', text: "D5W (5% Dextrose in Water)" },
        { id: 'b', text: "0.9% Normal Saline" },
        { id: 'c', text: "0.45% Normal Saline" },
        { id: 'd', text: "Lactated Ringer's Solution" }
      ],
      correctAnswer: 'b',
      explanation: "0.9% Normal Saline is isotonic and most appropriate for initial volume replacement in severe dehydration. D5W is hypotonic and would not be effective for volume replacement."
    },
    {
      id: 2,
      question: "A patient with diabetes is experiencing hypoglycemia. What is the appropriate initial intervention?",
      options: [
        { id: 'a', text: "Administer insulin" },
        { id: 'b', text: "Administer 50% dextrose IV" },
        { id: 'c', text: "Provide 15-20g of fast-acting carbohydrate if patient is conscious" },
        { id: 'd', text: "Administer glucagon IM" }
      ],
      correctAnswer: 'c',
      explanation: "For a conscious patient with hypoglycemia, the initial intervention is to provide 15-20g of fast-acting carbohydrate (juice, glucose tablets, etc.), then recheck blood glucose in 15 minutes."
    },
    {
      id: 3,
      question: "Which of the following assessment findings would indicate a potential complication of a blood transfusion?",
      options: [
        { id: 'a', text: "Temperature of 99.0°F" },
        { id: 'b', text: "Blood pressure of 120/80 mmHg" },
        { id: 'c', text: "Urticaria and itching" },
        { id: 'd', text: "Heart rate of 72 bpm" }
      ],
      correctAnswer: 'c',
      explanation: "Urticaria (hives) and itching are signs of a transfusion reaction, which is a potential complication of blood transfusion that requires immediate intervention."
    },
    {
      id: 4,
      question: "A patient is receiving digoxin therapy. Which electrolyte should be closely monitored?",
      options: [
        { id: 'a', text: "Sodium" },
        { id: 'b', text: "Potassium" },
        { id: 'c', text: "Chloride" },
        { id: 'd', text: "Bicarbonate" }
      ],
      correctAnswer: 'b',
      explanation: "Potassium levels should be closely monitored in patients receiving digoxin therapy. Hypokalemia increases the risk of digoxin toxicity, while hyperkalemia can enhance the cardiac effects of digoxin."
    },
    {
      id: 5,
      question: "Which nursing intervention is most appropriate for a patient with a stage 2 pressure ulcer on the sacrum?",
      options: [
        { id: 'a', text: "Apply a heating pad to increase circulation" },
        { id: 'b', text: "Massage the area around the ulcer" },
        { id: 'c', text: "Reposition the patient every 2 hours" },
        { id: 'd', text: "Apply a tight dressing to protect the wound" }
      ],
      correctAnswer: 'c',
      explanation: "Repositioning the patient every 2 hours is essential to relieve pressure and prevent further tissue damage. Heating pads and massage are contraindicated for pressure ulcers, and tight dressings can impair circulation."
    },
    {
      id: 6,
      question: "A patient is receiving oxygen therapy via nasal cannula at 2 L/min. What approximate FiO2 (fraction of inspired oxygen) is being delivered?",
      options: [
        { id: 'a', text: "24-28%" },
        { id: 'b', text: "35-40%" },
        { id: 'c', text: "50-60%" },
        { id: 'd', text: "90-100%" }
      ],
      correctAnswer: 'a',
      explanation: "A nasal cannula at 2 L/min delivers approximately 24-28% FiO2. Each additional liter increases the FiO2 by about 4%."
    },
    {
      id: 7,
      question: "Which of the following is a priority nursing intervention for a patient with acute pulmonary edema?",
      options: [
        { id: 'a', text: "Administer a sedative" },
        { id: 'b', text: "Place the patient in a supine position" },
        { id: 'c', text: "Position the patient upright with legs dependent" },
        { id: 'd', text: "Encourage increased fluid intake" }
      ],
      correctAnswer: 'c',
      explanation: "Positioning the patient upright with legs dependent (high Fowler's position) helps decrease venous return to the heart and lungs, reducing pulmonary congestion and improving breathing."
    },
    {
      id: 8,
      question: "A patient with heart failure is prescribed furosemide (Lasix). Which electrolyte should be monitored closely?",
      options: [
        { id: 'a', text: "Calcium" },
        { id: 'b', text: "Magnesium" },
        { id: 'c', text: "Potassium" },
        { id: 'd', text: "Phosphorus" }
      ],
      correctAnswer: 'c',
      explanation: "Furosemide is a loop diuretic that causes increased excretion of potassium, potentially leading to hypokalemia. Potassium levels should be monitored closely."
    },
    {
      id: 9,
      question: "Which of the following is an early sign of increased intracranial pressure?",
      options: [
        { id: 'a', text: "Bradycardia" },
        { id: 'b', text: "Headache and vomiting" },
        { id: 'c', text: "Widened pulse pressure" },
        { id: 'd', text: "Decerebrate posturing" }
      ],
      correctAnswer: 'b',
      explanation: "Headache and vomiting are early signs of increased intracranial pressure. Bradycardia, widened pulse pressure, and decerebrate posturing are late signs of increased ICP."
    },
    {
      id: 10,
      question: "A patient is receiving heparin therapy. Which laboratory value should be monitored to evaluate the effectiveness of therapy?",
      options: [
        { id: 'a', text: "Prothrombin Time (PT)" },
        { id: 'b', text: "International Normalized Ratio (INR)" },
        { id: 'c', text: "Activated Partial Thromboplastin Time (aPTT)" },
        { id: 'd', text: "Platelet count" }
      ],
      correctAnswer: 'c',
      explanation: "Activated Partial Thromboplastin Time (aPTT) is used to monitor unfractionated heparin therapy. PT and INR are used to monitor warfarin therapy."
    }
  ];

  // Sample questions for other assessment types would be defined similarly

  useEffect(() => {
    // In a real implementation, questions would be fetched from an API
    // For demonstration, we'll use the sample questions
    setIsLoading(false);
  }, []);

  const handleAssessmentTypeChange = (e) => {
    const selectedType = e.target.value;
    setAssessmentType(selectedType);
    setCurrentQuestion(0);
    setSelectedAnswers({});
    setAssessmentComplete(false);
    setAssessmentResult(null);
    
    // Load questions based on selected assessment type
    if (selectedType === 'rn_general') {
      setQuestions(rnGeneralQuestions);
    } else {
      // For demonstration, we'll use the same questions for all types
      // In a real implementation, different question sets would be loaded
      setQuestions(rnGeneralQuestions);
    }
  };

  const handleAnswerSelect = (questionId, answerId) => {
    setSelectedAnswers(prev => ({
      ...prev,
      [questionId]: answerId
    }));
  };

  const handleNextQuestion = () => {
    if (currentQuestion < questions.length - 1) {
      setCurrentQuestion(prev => prev + 1);
    } else {
      // Calculate results
      calculateResults();
    }
  };

  const handlePreviousQuestion = () => {
    if (currentQuestion > 0) {
      setCurrentQuestion(prev => prev - 1);
    }
  };

  const calculateResults = () => {
    setIsLoading(true);
    
    // Calculate score
    let correctCount = 0;
    questions.forEach(question => {
      if (selectedAnswers[question.id] === question.correctAnswer) {
        correctCount++;
      }
    });
    
    const score = (correctCount / questions.length) * 100;
    let ranking = '';
    
    // Determine ranking based on score
    if (score >= 90) {
      ranking = 'Expert';
    } else if (score >= 80) {
      ranking = 'Advanced';
    } else if (score >= 70) {
      ranking = 'Intermediate';
    } else {
      ranking = 'Beginner';
    }
    
    // Set assessment result
    setAssessmentResult({
      score,
      correctCount,
      totalQuestions: questions.length,
      ranking
    });
    
    setAssessmentComplete(true);
    setIsLoading(false);
  };

  const renderQuestion = () => {
    if (questions.length === 0) return null;
    
    const question = questions[currentQuestion];
    
    return (
      <Card bg={cardBg} borderWidth="1px" borderColor={borderColor} borderRadius="lg" shadow="md">
        <CardHeader>
          <Heading size="md">Question {currentQuestion + 1} of {questions.length}</Heading>
        </CardHeader>
        <CardBody>
          <VStack spacing={6} align="stretch">
            <Text fontWeight="bold">{question.question}</Text>
            
            <VStack spacing={3} align="stretch">
              {question.options.map(option => (
                <Button
                  key={option.id}
                  variant={selectedAnswers[question.id] === option.id ? "solid" : "outline"}
                  colorScheme={selectedAnswers[question.id] === option.id ? "blue" : "gray"}
                  justifyContent="flex-start"
                  textAlign="left"
                  height="auto"
                  py={3}
                  whiteSpace="normal"
                  onClick={() => handleAnswerSelect(question.id, option.id)}
                >
                  <Text>{option.id.toUpperCase()}. {option.text}</Text>
                </Button>
              ))}
            </VStack>
          </VStack>
        </CardBody>
        <CardFooter>
          <HStack spacing={4} width="100%" justifyContent="space-between">
            <Button 
              onClick={handlePreviousQuestion} 
              isDisabled={currentQuestion === 0}
              variant="outline"
            >
              Previous
            </Button>
            <Text>{currentQuestion + 1} of {questions.length}</Text>
            <Button 
              colorScheme="blue" 
              onClick={handleNextQuestion}
              isDisabled={!selectedAnswers[question.id]}
            >
              {currentQuestion === questions.length - 1 ? "Finish" : "Next"}
            </Button>
          </HStack>
        </CardFooter>
      </Card>
    );
  };

  const renderResults = () => {
    if (!assessmentResult) return null;
    
    return (
      <Card bg={cardBg} borderWidth="1px" borderColor={borderColor} borderRadius="lg" shadow="md">
        <CardHeader>
          <Heading size="md">Assessment Results</Heading>
        </CardHeader>
        <CardBody>
          <VStack spacing={6} align="center">
            <Box 
              borderRadius="full" 
              bg={assessmentResult.score >= 70 ? "green.100" : "red.100"} 
              p={6}
              width="150px"
              height="150px"
              display="flex"
              alignItems="center"
              justifyContent="center"
              flexDirection="column"
            >
              <Heading size="xl">{Math.round(assessmentResult.score)}%</Heading>
              <Text>Score</Text>
            </Box>
            
            <VStack>
              <Text>You answered {assessmentResult.correctCount} out of {assessmentResult.totalQuestions} questions correctly.</Text>
              <Badge colorScheme="blue" fontSize="lg" p={2}>
                Skill Ranking: {assessmentResult.ranking}
              </Badge>
            </VStack>
            
            <Divider />
            
            <Heading size="md">Question Review</Heading>
            
            <VStack spacing={4} align="stretch" width="100%">
              {questions.map((question, index) => (
                <Box 
                  key={question.id} 
                  p={4} 
                  borderRadius="md" 
                  bg={selectedAnswers[question.id] === question.correctAnswer ? correctBg : incorrectBg}
                >
                  <Flex justify="space-between" align="center" mb={2}>
                    <Heading size="sm">Question {index + 1}</Heading>
                    <Icon 
                      as={selectedAnswers[question.id] === question.correctAnswer ? FaCheckCircle : FaTimesCircle} 
                      color={selectedAnswers[question.id] === question.correctAnswer ? "green.500" : "red.500"} 
                    />
                  </Flex>
                  <Text mb={2}>{question.question}</Text>
                  <Text fontWeight="bold">
                    Your answer: {question.options.find(o => o.id === selectedAnswers[question.id])?.text || "Not answered"}
                  </Text>
                  {selectedAnswers[question.id] !== question.correctAnswer && (
                    <Text fontWeight="bold" color="green.600">
                      Correct answer: {question.options.find(o => o.id === question.correctAnswer)?.text}
                    </Text>
                  )}
                  <Text fontSize="sm" mt={2} fontStyle="italic">
                    {question.explanation}
                  </Text>
                </Box>
              ))}
            </VStack>
          </VStack>
        </CardBody>
        <CardFooter>
          <VStack spacing={4} width="100%">
            <Button colorScheme="blue" width="100%" onClick={() => navigate('/skills')}>
              Save and Return to Skills
            </Button>
            <Button variant="outline" width="100%" onClick={() => {
              setCurrentQuestion(0);
              setSelectedAnswers({});
              setAssessmentComplete(false);
              setAssessmentResult(null);
            }}>
              Retake Assessment
            </Button>
          </VStack>
        </CardFooter>
      </Card>
    );
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
            <Heading size="lg">Skills Assessment</Heading>
            <Text color="gray.600">Test your healthcare knowledge and skills</Text>
          </Box>
          
          {!assessmentType && (
     
(Content truncated due to size limit. Use line ranges to read in chunks)