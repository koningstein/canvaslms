<?php

namespace App\Livewire;

use App\Services\CanvasService;
use Livewire\Component;

class AssignmentGroupSelector extends Component
{
    public $selectedCourses = [];
    public $selectedModules = [];
    public $availableAssignmentGroups = [];
    public $selectedAssignmentGroups = [];

    protected CanvasService $canvasService;

    public function boot(CanvasService $canvasService)
    {
        $this->canvasService = $canvasService;
    }

    public function mount($selectedCourses = [], $selectedModules = [])
    {
        $this->selectedCourses = $selectedCourses;
        $this->selectedModules = $selectedModules;
        $this->loadAssignmentGroups();
    }

    public function loadAssignmentGroups()
    {
        $assignmentGroups = [];

        // Debug: log wat we beginnen
        \Log::info('LoadAssignmentGroups gestart', [
            'selected_modules' => count($this->selectedModules),
            'selected_courses' => count($this->selectedCourses)
        ]);

        // Verzamel alle assignment IDs uit de geselecteerde modules
        $moduleAssignmentIds = $this->getAssignmentIdsFromModules();

        \Log::info('Assignment IDs uit modules', [
            'assignment_ids' => $moduleAssignmentIds,
            'count' => count($moduleAssignmentIds)
        ]);

        // Voor elke geselecteerde cursus, haal de assignment groups op
        foreach ($this->selectedCourses as $course) {
            $courseAssignmentGroups = $this->canvasService->getAssignmentGroups($course['id']);

            \Log::info('Assignment groups voor cursus', [
                'course_id' => $course['id'],
                'groups_count' => count($courseAssignmentGroups)
            ]);

            foreach ($courseAssignmentGroups as $group) {
                // Filter assignments die daadwerkelijk in de geselecteerde modules zitten
                $filteredAssignments = [];
                if (isset($group['assignments'])) {
                    $filteredAssignments = array_filter($group['assignments'], function($assignment) use ($moduleAssignmentIds) {
                        return in_array($assignment['id'], $moduleAssignmentIds);
                    });
                }

                \Log::info('Group filtering', [
                    'group_name' => $group['name'],
                    'total_assignments' => isset($group['assignments']) ? count($group['assignments']) : 0,
                    'filtered_assignments' => count($filteredAssignments),
                    'group_assignment_ids' => isset($group['assignments']) ? array_column($group['assignments'], 'id') : []
                ]);

                // Alleen toevoegen als er assignments in de modules zitten
                if (!empty($filteredAssignments)) {
                    $assignmentGroups[] = array_merge($group, [
                        'course_id' => $course['id'],
                        'course_name' => $course['name'],
                        'assignments' => array_values($filteredAssignments) // Herindexeer array
                    ]);
                }
            }
        }

        \Log::info('Finale assignment groups', [
            'final_count' => count($assignmentGroups)
        ]);

        // Fallback: als er geen gefilterde groups zijn, toon alle groups
        if (empty($assignmentGroups)) {
            \Log::warning('Geen gefilterde assignment groups gevonden, toon alle groups als fallback');
            foreach ($this->selectedCourses as $course) {
                $courseAssignmentGroups = $this->canvasService->getAssignmentGroups($course['id']);
                foreach ($courseAssignmentGroups as $group) {
                    $assignmentGroups[] = array_merge($group, [
                        'course_id' => $course['id'],
                        'course_name' => $course['name']
                    ]);
                }
            }
        }

        $this->availableAssignmentGroups = $assignmentGroups;
    }

    private function getAssignmentIdsFromModules()
    {
        $assignmentIds = [];

        foreach ($this->selectedModules as $module) {
            try {
                \Log::info('Ophalen module items', [
                    'module_id' => $module['id'],
                    'course_id' => $module['course_id']
                ]);

                // Haal module items op voor elke geselecteerde module
                $moduleItems = $this->canvasService->getModuleItems($module['course_id'], $module['id']);

                \Log::info('Module items opgehaald', [
                    'module_id' => $module['id'],
                    'items_count' => count($moduleItems)
                ]);

                foreach ($moduleItems as $item) {
                    \Log::info('Module item', [
                        'type' => $item['type'] ?? 'unknown',
                        'content_id' => $item['content_id'] ?? 'none',
                        'title' => $item['title'] ?? 'no title'
                    ]);

                    // Alleen assignments uit modules
                    if ($item['type'] === 'Assignment' && isset($item['content_id'])) {
                        $assignmentIds[] = $item['content_id'];
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Fout bij ophalen module items', [
                    'module_id' => $module['id'],
                    'course_id' => $module['course_id'],
                    'error' => $e->getMessage()
                ]);
            }
        }

        $uniqueIds = array_unique($assignmentIds);
        \Log::info('Assignment IDs verzameld', [
            'all_ids' => $assignmentIds,
            'unique_ids' => $uniqueIds
        ]);

        return $uniqueIds;
    }

    public function selectAssignmentGroup($groupIndex)
    {
        $group = $this->availableAssignmentGroups[$groupIndex];
        $this->selectedAssignmentGroups[] = $group;
        unset($this->availableAssignmentGroups[$groupIndex]);
        $this->availableAssignmentGroups = array_values($this->availableAssignmentGroups);
    }

    public function deselectAssignmentGroup($groupIndex)
    {
        $group = $this->selectedAssignmentGroups[$groupIndex];
        $this->availableAssignmentGroups[] = $group;
        unset($this->selectedAssignmentGroups[$groupIndex]);
        $this->selectedAssignmentGroups = array_values($this->selectedAssignmentGroups);
    }

    public function selectAllAssignmentGroups()
    {
        $this->selectedAssignmentGroups = array_merge($this->selectedAssignmentGroups, $this->availableAssignmentGroups);
        $this->availableAssignmentGroups = [];
    }

    public function deselectAllAssignmentGroups()
    {
        $this->availableAssignmentGroups = array_merge($this->availableAssignmentGroups, $this->selectedAssignmentGroups);
        $this->selectedAssignmentGroups = [];
    }

    public function proceedToStudents()
    {
        session([
            'selected_courses' => $this->selectedCourses,
            'selected_modules' => $this->selectedModules,
            'selected_assignment_groups' => $this->selectedAssignmentGroups,
        ]);

        return redirect()->route('students.select');
    }

    public function render()
    {
        return view('livewire.assignment-group-selector');
    }
}
