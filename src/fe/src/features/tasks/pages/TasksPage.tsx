import { useState, useEffect } from 'react';
import { Plus } from 'lucide-react';
import { Button } from '@/shared/components/ui/button';
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
} from '@/shared/components/ui/dialog';
import { useTasksStore } from '@features/tasks/store/tasks.store.ts';
import { TaskList } from '@features/tasks/components/TaskList.tsx';
import { CreateTaskForm } from '@features/tasks/components/CreateTaskForm.tsx';

export const TasksPage = () => {
  const [isCreateModalOpen, setIsCreateModalOpen] = useState(false);
  const { isInitialized, fetchTasks } = useTasksStore();

  useEffect(() => {
    if (!isInitialized) {
      console.log('Initializing tasks page');
      fetchTasks(1);
    }
  }, [isInitialized, fetchTasks]);

  return (
    <div className="container mx-auto py-6">
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-2xl font-bold text-gray-900">Tasks</h1>
        <Button
          onClick={() => setIsCreateModalOpen(true)}
          className="flex items-center gap-2"
        >
          <Plus size={16} />
          New Task
        </Button>

      </div>

      <TaskList />

      <Dialog
        open={isCreateModalOpen}
        onOpenChange={setIsCreateModalOpen}
      >
        <DialogContent className="sm:max-w-[600px]">
          <DialogHeader>
            <DialogTitle>Create New Task</DialogTitle>
            <DialogDescription>
              Fill in the details below to create a new task. All fields marked with * are required.
            </DialogDescription>
          </DialogHeader>
          <CreateTaskForm onClose={() => setIsCreateModalOpen(false)} />
        </DialogContent>
      </Dialog>
    </div>
  );
};