<?php

namespace App\Livewire;

use Livewire\Component;

class ResultSelector extends Component
{
    public $selectedCourses = [];
    public $selectedModules = [];
    public $selectedAssignmentGroups = [];
    public $selectedUsers = [];

    public function mount($selectedCourses = [], $selectedModules = [], $selectedAssignmentGroups = [], $selectedUsers = [])
    {
        $this->selectedCourses = $selectedCourses;
        $this->selectedModules = $selectedModules;
        $this->selectedAssignmentGroups = $selectedAssignmentGroups;
        $this->selectedUsers = $selectedUsers;
    }

    public function generateBasicReport()
    {
        $this->setSessionAndRedirect('basic');
    }

    public function generateGradeReport()
    {
        $this->setSessionAndRedirect('grades');
    }

    public function generatePercentageReport()
    {
        $this->setSessionAndRedirect('percentages');
    }

    public function generateMissingReport()
    {
        $this->setSessionAndRedirect('missing');
    }

    public function generateDeadlineReport()
    {
        $this->setSessionAndRedirect('deadlines');
    }

    public function generateAttentionReport()
    {
        $this->setSessionAndRedirect('attention');
    }

    public function generateAverageReport()
    {
        $this->setSessionAndRedirect('averages');
    }

    public function generateTimelineReport()
    {
        $this->setSessionAndRedirect('timeline');
    }

    public function generateCompetencyReport()
    {
        $this->setSessionAndRedirect('competency');
    }

    public function generateExcelExport()
    {
        $this->setSessionAndRedirect('excel');
    }

    private function setSessionAndRedirect($reportType)
    {
        session([
            'selected_courses' => $this->selectedCourses,
            'selected_modules' => $this->selectedModules,
            'selected_assignment_groups' => $this->selectedAssignmentGroups,
            'selected_users' => $this->selectedUsers,
            'report_type' => $reportType
        ]);

        return redirect()->route('results.progress');
    }

    public function render()
    {
        return view('livewire.result-selector');
    }
}
