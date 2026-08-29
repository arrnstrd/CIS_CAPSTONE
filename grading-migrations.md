# Grading System Migration Schemas

## grading_periods
- id (bigIncrements)
- name (string)
- sequence (unsignedTinyInteger, unique)
- is_active (boolean, default true)
- timestamps

## assessment_categories
- id (bigIncrements)
- name (string)
- weight (decimal)
- timestamps

## teaching_assignments
- id (bigIncrements)
- subject_id (foreignId)
- section_id (foreignId)
- teacher_id (foreignId)
- school_year_id (foreignId)
- timestamps

## assessments
- id (bigIncrements)
- teaching_assignment_id (foreignId)
- grading_period_id (foreignId)
- assessment_category_id (foreignId)
- name (string)
- total_items (integer)
- status (string)
- timestamps

## student_assessment_scores
- id (bigIncrements)
- assessment_id (foreignId)
- enrollment_id (foreignId)
- score (decimal)
- remarks (text, nullable)
- timestamps

## quarterly_grades
- id (bigIncrements)
- teaching_assignment_id (foreignId)
- enrollment_id (foreignId)
- grading_period_id (foreignId)
- written_work_grade (decimal, nullable)
- performance_task_grade (decimal, nullable)
- quarterly_assessment_grade (decimal, nullable)
- initial_grade (decimal, nullable)
- transmuted_grade (decimal, nullable)
- timestamps

## grading_configs
- id (bigIncrements)
- name (string)
- value (json/text)
- timestamps
