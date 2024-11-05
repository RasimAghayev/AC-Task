import { useState } from 'react';
import { useTasksStore } from '@features/tasks/store/tasks.store.ts';
import { TaskPriority, TaskStatus, TaskFilters as TaskFiltersType } from '@features/tasks/types';

const statusOptions = [
  { value: TaskStatus.PENDING, label: 'Pending' },
  { value: TaskStatus.IN_PROGRESS, label: 'In Progress' },
  { value: TaskStatus.UNDER_REVIEW, label: 'Under Review' },
  { value: TaskStatus.COMPLETED, label: 'Completed' },
  { value: TaskStatus.CANCELLED, label: 'Cancelled' },
];

const priorityOptions = [
  { value: TaskPriority.LOW, label: 'Low' },
  { value: TaskPriority.MEDIUM, label: 'Medium' },
  { value: TaskPriority.HIGH, label: 'High' },
  { value: TaskPriority.URGENT, label: 'Urgent' },
];

export const TaskFilters = () => {
  const { setFilters, resetFilters } = useTasksStore();
  const [localFilters, setLocalFilters] = useState<TaskFiltersType>({});
  const [selectedStatus, setSelectedStatus] = useState<TaskStatus | undefined>();
  const [selectedPriority, setSelectedPriority] = useState<TaskPriority | undefined>();
  const [startDate, setStartDate] = useState<string>('');
  const [endDate, setEndDate] = useState<string>('');

  const handleApplyFilters = () => {
    setFilters(localFilters);
  };
  const handleReset = () => {
    setLocalFilters({});
    resetFilters();
  };


  const handleStatusChange = (value: string) => {
    setLocalFilters(prev => ({
      ...prev,
      status: value ? { eq: value as TaskStatus } : undefined
    }));
  };

  const handlePriorityChange = (value: string) => {
    setSelectedPriority(value ? value as TaskPriority : undefined);
  };

  return (
    <div className="p-4 bg-white rounded shadow">
      <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <div>
          <label className="block text-sm font-medium mb-1">Status</label>
          <select
            value={selectedStatus || ''}
            onChange={(e) => handleStatusChange(e.target.value)}
            className="w-full p-2 border rounded"
          >
            <option value="">All Status</option>
            {statusOptions.map(option => (
              <option key={option.value} value={option.value}>
                {option.label}
              </option>
            ))}
          </select>
        </div>

        <div>
          <label className="block text-sm font-medium mb-1">Priority</label>
          <select
            value={selectedPriority || ''}
            onChange={(e) => handlePriorityChange(e.target.value)}
            className="w-full p-2 border rounded"
          >
            <option value="">All Priority</option>
            {priorityOptions.map(option => (
              <option key={option.value} value={option.value}>
                {option.label}
              </option>
            ))}
          </select>
        </div>

        <div>
          <label className="block text-sm font-medium mb-1">Start Date</label>
          <input
            type="date"
            value={startDate}
            onChange={(e) => setStartDate(e.target.value)}
            className="w-full p-2 border rounded"
          />
        </div>

        <div>
          <label className="block text-sm font-medium mb-1">End Date</label>
          <input
            type="date"
            value={endDate}
            onChange={(e) => setEndDate(e.target.value)}
            className="w-full p-2 border rounded"
          />
        </div>
      </div>

      <div className="mt-4 flex justify-end gap-2">
        <button
          onClick={handleReset}
          className="px-4 py-2 text-sm border rounded hover:bg-gray-50"
        >
          Reset
        </button>
        <button
          onClick={handleApplyFilters}
          className="px-4 py-2 text-sm text-white bg-blue-600 rounded hover:bg-blue-700"
        >
          Apply Filters
        </button>
      </div>
    </div>
  );
};