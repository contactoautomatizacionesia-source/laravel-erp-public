<?php
return [

    'created' => 'The role work schedule has been created successfully',
    'updated' => 'The role work schedule has been updated successfully',
    'deleted' => 'The role work schedule has been deleted successfully',
    'errors' => [
        'error_message' => 'Error saving changes.',
        'outside_schedule' => 'This is outside of his working hours',
        'role_not_found' => 'The role with the id :id was not found',
        'user_found' => 'The schedule cannot be deleted because it is associated with an active role.',
        'not_found' => 'The role work schedule with the id :id was not found',
        'index_db' => 'An error occurred with the database while retrieving the work schedule of the roles',
        'create_validation' => 'The role work schedule could not be created because it does not have the correct fields',
        'create_db' => 'An error occurred with the database while creating a role work schedule',
        'update_db' => 'An error occurred with the database while updating the role work schedule with the id :id',
        'delete_db' => 'An error occurred with the database while deleting the role work schedule with the id :id',
        'index_unknown' => 'An error occurred while retrieving the work schedule of the roles. Error: :error',
        'create_unknown' => 'An error occurred while creating a role work schedule. Error: :error',
        'update_unknown' => 'An error occurred while updating the role work schedule with the id :id. Error: :error',
        'delete_unknown' => 'An error occurred while deleting the role work schedule with the id :id. Error: :error',
        'holiday_restricted' => 'You do not have permission to enter on holidays',
        'end_time_must_be_after_start_time' => 'The end time must be later than the start time.'
    ],
    'schedules_assigned_correctly' => 'Schedules assigned correctly',
    'updated_global_schedules' => 'Updated global schedules',
    'assign_schedule_to' => 'Assign Work Schedules to',
    'back_to_schedule_setup' => 'Back to Schedule Setup',
    'add_range' => 'Add Range',
    'holiday_access' => 'Enable system access on holidays',
    'no_changes_detected' => 'No changes were detected',
    'there_not_available_times_this_category' => 'There are no available times for this category.'

];
