import { useEffect, useMemo, useState } from 'react';
import { Badge } from '@/shared/components/ui/badge';
import { Input } from '@/shared/components/ui/input';
import { Button } from '@/shared/components/ui/button';
import { Search, X, Pencil, Trash2 } from 'lucide-react';
import { useTasksStore } from '@features/tasks/store/tasks.store';
import { TaskFilters } from '@features/tasks/components/TaskFilters';
import { formatDate } from '@features/tasks/utils/date';
import { Task } from '@features/tasks/types';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@shared/components/ui/dialog';
import { UpdateTaskForm } from '@features/tasks/components/UpdateTaskForm';

export const TaskList = () => {
  const {
    tasks,
    isLoading,
    isSearching,
    error,
    meta,
    currentPage,
    searchQuery,
    hasActiveFilters,
    setSearchQuery,
    initialized,
    fetchTasks,
    resetFilters,
    deleteTask
  } = useTasksStore();

  const [selectedTask, setSelectedTask] = useState<Task | null>(null);
  const [isUpdateModalOpen, setIsUpdateModalOpen] = useState(false);

  useEffect(() => {
    if (!initialized) {
      fetchTasks(1);
    }
  }, [initialized]);

  const handleTaskClick = (task: Task) => {
    setSelectedTask(task);
    setIsUpdateModalOpen(true);
  };

  const handleDeleteClick = async (e: React.MouseEvent, taskId: number) => {
    e.stopPropagation();
    if (window.confirm('Are you sure you want to delete this task?')) {
      try {
        await deleteTask(taskId);
      } catch (error) {
        console.error('Failed to delete task:', error);
      }
    }
  };

  const showEmptyState = useMemo(() =>
      !isLoading && (tasks?.length === 0),
    [isLoading, tasks?.length]
  );

  const showLoadingState = useMemo(() =>
      isLoading && !isSearching,
    [isLoading, isSearching]
  );

  if (error) {
    return (
      <div className="bg-red-50 p-4 rounded-lg text-red-500">
        Error: {error}
      </div>
    );
  }
  if (isLoading && !tasks.length) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900" />
      </div>
    );
  }

  return (
    <div className="space-y-6">
      {/* Search & Filters */}
      <div className="flex gap-4 items-center">
        <div className="relative flex-1">
          <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <Search className="h-5 w-5 text-gray-400" />
          </div>
          <Input
            type="text"
            placeholder="Search tasks..."
            className="pl-10 w-full"
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
          />
          {searchQuery && (
            <button
              className="absolute inset-y-0 right-0 pr-3 flex items-center"
              onClick={() => setSearchQuery('')}
            >
              <X className="h-5 w-5 text-gray-400 hover:text-gray-600" />
            </button>
          )}
        </div>

        <TaskFilters />

        {hasActiveFilters && (
          <Button
            variant="outline"
            size="sm"
            onClick={resetFilters}
          >
            Reset Filters
          </Button>
        )}
      </div>

      {/* Loading State */}
      {showLoadingState && (
        <div className="flex items-center justify-center h-64">
          <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900" />
        </div>
      )}

      {/* Empty State */}
      {showEmptyState && (
        <div className="text-center py-10">
          <p className="text-gray-500">
            {searchQuery
              ? `No tasks found for "${searchQuery}"`
              : hasActiveFilters
                ? "No tasks match your filters"
                : "No tasks available"}
          </p>
        </div>
      )}

      {/* Tasks List */}
      {!showEmptyState && (
        <div className="grid gap-4">
          {tasks?.map((task) => (
            <div
              key={task.id}
              className="bg-white rounded-lg shadow-sm p-4 hover:shadow-md transition-shadow"
            >
              <div className="flex items-start justify-between">
                <div className="space-y-1">
                  <h3 className="font-medium text-gray-900">{task.title}</h3>
                  <p className="text-sm text-gray-500 line-clamp-2">
                    {task.description}
                  </p>
                </div>
                <div className="flex items-start gap-3">
                  <Badge
                    style={{ backgroundColor: task.status.color }}
                  >
                    {task.status.label}
                  </Badge>
                  <div className="flex gap-1">
                    <Button
                      variant="ghost"
                      size="sm"
                      onClick={() => handleTaskClick(task)}
                      className="h-8 w-8 p-0"
                    >
                      <Pencil className="h-4 w-4 text-blue-500" />
                    </Button>
                    <Button
                      variant="ghost"
                      size="sm"
                      onClick={(e) => handleDeleteClick(e, task.id)}
                      className="h-8 w-8 p-0"
                    >
                      <Trash2 className="h-4 w-4 text-red-500" />
                    </Button>
                  </div>
                </div>
              </div>

              <div className="mt-4 flex flex-wrap items-center gap-2 text-sm">
                <div className="flex items-center gap-2">
                  <span className="text-gray-500">Due:</span>
                  <span className="font-medium">
                    {formatDate(task.dueDate)}
                  </span>
                </div>

                <div className="flex items-center gap-2">
                  <span className="text-gray-500">Priority:</span>
                  <Badge
                    style={{ backgroundColor: task.priority.color }}
                    variant="outline"
                  >
                    {task.priority.label}
                  </Badge>
                </div>

                <div className="flex items-center gap-2">
                  <span className="text-gray-500">Repeat:</span>
                  <Badge
                    style={{ backgroundColor: task.repeatType.color }}
                    variant="outline"
                  >
                    {task.repeatType.label}
                  </Badge>
                </div>
              </div>

              {task.tags.length > 0 && (
                <div className="mt-3 flex flex-wrap gap-1">
                  {task.tags.map((tag) => (
                    <span
                      key={tag}
                      className="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800"
                    >
                      #{tag}
                    </span>
                  ))}
                </div>
              )}
            </div>
          ))}
        </div>
      )}

      {/* Pagination */}
      {meta && !showEmptyState && (
        <div className="flex justify-between items-center pt-4 border-t">
          <div className="text-sm text-gray-500">
            Showing {meta.from} to {meta.to} of {meta.total} results
          </div>
          <div className="flex gap-2">
            <Button
              variant="outline"
              size="sm"
              onClick={() => fetchTasks(currentPage - 1)}
              disabled={currentPage === 1}
            >
              Previous
            </Button>
            <Button
              variant="outline"
              size="sm"
              onClick={() => fetchTasks(currentPage + 1)}
              disabled={currentPage === meta.last_page}
            >
              Next
            </Button>
          </div>
        </div>
      )}

      {/* Update Modal */}
      <Dialog open={isUpdateModalOpen} onOpenChange={setIsUpdateModalOpen}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Update Task</DialogTitle>
          </DialogHeader>
          {selectedTask && (
            <UpdateTaskForm
              task={selectedTask}
              onClose={() => setIsUpdateModalOpen(false)}
            />
          )}
        </DialogContent>
      </Dialog>
    </div>
  );
};