<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompetencySeeder extends Seeder
{
    public function run(): void
    {
        $competencies = [
            ['competency_key' => 'exemplifying_integrity', 'competency_name' => 'Exemplifying Integrity', 'competency_group' => 'Core', 'sort_order' => 1, 'description' => 'The ability to exemplify high standards of professional behavior as public servants, adhering to ethical as well as moral principles, values and standards of public office.'],
            ['competency_key' => 'delivering_service_excellence', 'competency_name' => 'Delivering Service Excellence', 'competency_group' => 'Core', 'sort_order' => 2, 'description' => 'The ability to provide proactive, responsive, accessible, courteous and effective public service to attain the highest level of customer satisfaction.'],
            ['competency_key' => 'solving_problems_making_decisions', 'competency_name' => 'Solving Problems and Making Decisions', 'competency_group' => 'Core', 'sort_order' => 3, 'description' => 'The ability to resolve deviations and exercise good judgement by using fact-based analysis and generating and selecting appropriate courses of action to produce positive results.'],
            ['competency_key' => 'demonstrating_personal_effectiveness_1', 'competency_name' => 'Demonstrating Personal Effectiveness', 'competency_group' => 'Core', 'sort_order' => 4, 'description' => 'The ability to demonstrate and display self-direction or self-motivation as well as engaging in ongoing personal development.'],
            ['competency_key' => 'speaking_effectively_1', 'competency_name' => 'Speaking Effectively', 'competency_group' => 'Core', 'sort_order' => 5, 'description' => 'The ability to actively listen, understand and respond appropriately when interacting with individuals and groups.'],
            ['competency_key' => 'writing_effectively_1', 'competency_name' => 'Writing Effectively', 'competency_group' => 'Core', 'sort_order' => 6, 'description' => 'The ability to write in a clear, concise and coherent manner using different tools to convey information or express ideas effectively.'],
            ['competency_key' => 'championing_and_applying_innovation', 'competency_name' => 'Championing and Applying Innovation', 'competency_group' => 'Core', 'sort_order' => 7, 'description' => 'The ability to increase productivity and efficiency at work by applying new ideas and creative solutions to existing processes, methods, and services.'],
            ['competency_key' => 'planning_and_delivering', 'competency_name' => 'Planning and Delivering', 'competency_group' => 'Core', 'sort_order' => 8, 'description' => 'The ability to set priorities and identify scope and allocate resources to meet individual, team or organization targets and objectives.'],
            ['competency_key' => 'thinking_strategically', 'competency_name' => 'Thinking Strategically', 'competency_group' => 'Core', 'sort_order' => 9, 'description' => 'The ability to direct and establish short and long-range plans and calculate and manage risks based on future or emerging trends and outcomes of decisions to achieve CSC goals.'],

            ['competency_key' => 'demonstrating_personal_effectiveness', 'competency_name' => 'Demonstrating Personal Effectiveness', 'competency_group' => 'Organizational', 'sort_order' => 1, 'description' => null],
            ['competency_key' => 'championing_applying_innovation', 'competency_name' => 'Championing and Applying Innovation', 'competency_group' => 'Organizational', 'sort_order' => 2, 'description' => null],
            ['competency_key' => 'speaking_effectively', 'competency_name' => 'Speaking Effectively', 'competency_group' => 'Organizational', 'sort_order' => 3, 'description' => null],
            ['competency_key' => 'planning_delivering', 'competency_name' => 'Planning and Delivering', 'competency_group' => 'Organizational', 'sort_order' => 4, 'description' => null],
            ['competency_key' => 'writing_effectively', 'competency_name' => 'Writing Effectively', 'competency_group' => 'Organizational', 'sort_order' => 5, 'description' => null],
            ['competency_key' => 'managing_information', 'competency_name' => 'Managing Information', 'competency_group' => 'Organizational', 'sort_order' => 6, 'description' => null],

            ['competency_key' => 'managing_performance_coaching', 'competency_name' => 'Managing Performance and Coaching for Results', 'competency_group' => 'Leadership', 'sort_order' => 1, 'description' => 'The ability to provide timely and relevant feedback to individuals or groups in order for them to take action and improve their performance.'],
            ['competency_key' => 'thinking_strategically_creatively', 'competency_name' => 'Thinking Strategically and Creatively', 'competency_group' => 'Leadership', 'sort_order' => 2, 'description' => 'The ability to direct and establish short and long-range plans and calculate and manage risks based on future or emerging trends and outcomes of decisions to achieve CSC goals.'],
            ['competency_key' => 'building_collaborative_inclusive', 'competency_name' => 'Building Collaborative and Inclusive Working Relationship', 'competency_group' => 'Leadership', 'sort_order' => 3, 'description' => null],
            ['competency_key' => 'leading_change', 'competency_name' => 'Leading Change', 'competency_group' => 'Leadership', 'sort_order' => 4, 'description' => null],
            ['competency_key' => 'creating_nurturing_high_performing', 'competency_name' => 'Creating and Nurturing a High Performing Organization', 'competency_group' => 'Leadership', 'sort_order' => 5, 'description' => null],

            ['competency_key' => 'accounting', 'competency_name' => 'Accounting', 'competency_group' => 'Technical', 'sort_order' => 0, 'description' => null],
            ['competency_key' => 'policy_interpretation_implementation', 'competency_name' => 'Policy Interpretation and Implementation', 'competency_group' => 'Technical', 'sort_order' => 0, 'description' => null],
            ['competency_key' => 'audit_management', 'competency_name' => 'Audit Management', 'competency_group' => 'Technical', 'sort_order' => 0, 'description' => null],
            ['competency_key' => 'learning_delivery_evaluation', 'competency_name' => 'Learning Delivery and Evaluation', 'competency_group' => 'Technical', 'sort_order' => 0, 'description' => null],
            ['competency_key' => 'records_management', 'competency_name' => 'Records Management', 'competency_group' => 'Technical', 'sort_order' => 0, 'description' => null],
            ['competency_key' => 'test_administration', 'competency_name' => 'Test Administration', 'competency_group' => 'Technical', 'sort_order' => 0, 'description' => null],
            ['competency_key' => 'secretariat_liaison_services', 'competency_name' => 'Secretariat and Liaison Services', 'competency_group' => 'Technical', 'sort_order' => 0, 'description' => null],
            ['competency_key' => 'supplies_property_management', 'competency_name' => 'Supplies and Property Management', 'competency_group' => 'Technical', 'sort_order' => 0, 'description' => null],
            ['competency_key' => 'information_technology', 'competency_name' => 'Information Technology', 'competency_group' => 'Technical', 'sort_order' => 0, 'description' => null],
            ['competency_key' => 'legal_management', 'competency_name' => 'Legal Management', 'competency_group' => 'Technical', 'sort_order' => 0, 'description' => null],
        ];

        foreach ($competencies as $competency) {
            DB::table('competencies')->updateOrInsert(
                ['competency_key' => $competency['competency_key']],
                array_merge($competency, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
