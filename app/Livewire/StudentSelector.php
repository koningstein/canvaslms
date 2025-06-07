<?php

namespace App\Livewire;

use App\Services\CanvasService;
use Livewire\Component;

class StudentSelector extends Component
{
    public $selectedCourses = [];
    public $selectedModules = [];
    public $selectedAssignmentGroups = [];
    public $availableUsers = [];
    public $selectedUsers = [];
    public $availableSections = [];

    protected CanvasService $canvasService;

    public function boot(CanvasService $canvasService)
    {
        $this->canvasService = $canvasService;
    }

    public function mount($selectedCourses = [], $selectedModules = [], $selectedAssignmentGroups = [])
    {
        $this->selectedCourses = $selectedCourses;
        $this->selectedModules = $selectedModules;
        $this->selectedAssignmentGroups = $selectedAssignmentGroups;
        $this->loadSections();
        $this->loadUsers();
    }

    public function loadSections()
    {
        $sections = [];
        foreach ($this->selectedCourses as $course) {
            $courseSections = $this->canvasService->getCourseSections($course['id']);
            foreach ($courseSections as $section) {
                // Skip default section if it has the same name as the course
                if ($section['name'] === $course['name']) {
                    continue;
                }

                $sections[] = array_merge($section, [
                    'course_id' => $course['id'],
                    'course_name' => $course['name']
                ]);
            }
        }
        $this->availableSections = $sections;
    }

    public function loadUsers()
    {
        $users = [];
        foreach ($this->selectedCourses as $course) {
            $courseUsers = $this->canvasService->getUsers($course['id']);
            foreach ($courseUsers as $user) {
                $users[] = array_merge($user, ['course_id' => $course['id'], 'course_name' => $course['name']]);
            }
        }

        // Verwijder duplicaten op basis van id (betrouwbaarder dan email)
        $this->availableUsers = collect($users)->unique('id')->values()->toArray();
    }

    public function selectUser($userIndex)
    {
        $user = $this->availableUsers[$userIndex];

        // Prevent duplicates
        foreach ($this->selectedUsers as $selected) {
            if ($selected['id'] == $user['id']) {
                return;
            }
        }

        $this->selectedUsers[] = $user;
        unset($this->availableUsers[$userIndex]);
        $this->availableUsers = array_values($this->availableUsers);
    }

    public function deselectUser($userIndex)
    {
        $user = $this->selectedUsers[$userIndex];
        $this->availableUsers[] = $user;
        unset($this->selectedUsers[$userIndex]);
        $this->selectedUsers = array_values($this->selectedUsers);
    }

    public function selectAllUsers()
    {
        foreach ($this->availableUsers as $user) {
            // Check for duplicates
            $alreadySelected = false;
            foreach ($this->selectedUsers as $selected) {
                if ($selected['id'] == $user['id']) {
                    $alreadySelected = true;
                    break;
                }
            }
            if (!$alreadySelected) {
                $this->selectedUsers[] = $user;
            }
        }
        $this->availableUsers = [];
    }

    public function deselectAllUsers()
    {
        $this->availableUsers = array_merge($this->availableUsers, $this->selectedUsers);
        $this->selectedUsers = [];
    }

    public function selectUsersFromSection($sectionId, $sectionName)
    {
        try {
            $sectionUsers = $this->canvasService->getSectionUsers($sectionId);
            $addedCount = 0;

            foreach ($sectionUsers as $user) {
                // Check if user is already selected
                $alreadySelected = false;
                foreach ($this->selectedUsers as $selected) {
                    if ($selected['id'] == $user['id']) {
                        $alreadySelected = true;
                        break;
                    }
                }

                if (!$alreadySelected) {
                    // Add section info to user
                    $userWithSection = array_merge($user, [
                        'section_id' => $sectionId,
                        'section_name' => $sectionName
                    ]);

                    $this->selectedUsers[] = $userWithSection;
                    $addedCount++;

                    // Remove from available users if present
                    foreach ($this->availableUsers as $index => $availableUser) {
                        if ($availableUser['id'] == $user['id']) {
                            unset($this->availableUsers[$index]);
                            break;
                        }
                    }
                }
            }

            // Reindex available users array
            $this->availableUsers = array_values($this->availableUsers);

            // Show feedback
            if ($addedCount > 0) {
                session()->flash('success', "Er zijn {$addedCount} studenten uit sectie '{$sectionName}' toegevoegd.");
            } else {
                session()->flash('warning', "Alle studenten uit sectie '{$sectionName}' waren al geselecteerd.");
            }

        } catch (\Exception $e) {
            session()->flash('error', 'Fout bij het ophalen van studenten uit sectie: ' . $e->getMessage());
        }
    }

    public function showResult()
    {
        session([
            'selected_courses' => $this->selectedCourses,
            'selected_modules' => $this->selectedModules,
            'selected_assignment_groups' => $this->selectedAssignmentGroups,
            'selected_users' => $this->selectedUsers,
        ]);

        return redirect()->route('results.select');
    }

    public function render()
    {
        return view('livewire.student-selector');
    }
}
