import { useEffect } from 'react';
import { Badge } from '@/shared/components/ui/badge';
import { useTasksStore } from '@features/tasks/store/tasks.store.ts';

// Helper function for date formatting
const formatDate = (dateString: string) => {
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

export const TaskList = () => {
  const { tasks, isLoading, error, fetchTasks, meta, currentPage } = useTasksStore();

  useEffect(() => {
    fetchTasks();
  }, []);

  if (isLoading) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="bg-red-50 p-4 rounded-lg text-red-500">
        Error: {error}
      </div>
    );
  }

  return (
    <div className="space-y-6">
      {/* Tasks List */}
      <div className="grid gap-4">
        {tasks.map((task) => (
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
              <Badge
                style={{ backgroundColor: task.status.color }}
                className="ml-2"
              >
                {task.status.label}
              </Badge>
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

      {/* Pagination */}
      {meta && (
        <div className="flex justify-between items-center pt-4 border-t">
          <div className="text-sm text-gray-500">
            Showing {meta.from} to {meta.to} of {meta.total} results
          </div>
          <div className="flex gap-2">
            <button
              onClick={() => fetchTasks(currentPage - 1)}
              disabled={currentPage === 1}
              className="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50"
            >
              Previous
            </button>
            <button
              onClick={() => fetchTasks(currentPage + 1)}
              disabled={currentPage === meta.last_page}
              className="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50"
            >
              Next
            </button>
          </div>
        </div>
      )}
    </div>
  );
};