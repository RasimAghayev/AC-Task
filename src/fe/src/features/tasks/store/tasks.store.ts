import { create } from 'zustand';
import { CreateTaskDto, Task, TasksResponse } from '@features/tasks/types/task.types.ts';
import { TasksAPI } from '@features/api/tasks.api.ts';

interface TasksState {
  tasks: Task[];
  meta: TasksResponse['meta'] | null;
  isLoading: boolean;
  error: string | null;
  currentPage: number;
  isInitialized: boolean;
  fetchTasks: (page?: number) => Promise<void>;
  createTask: (data: CreateTaskDto) => Promise<void>;
}

export const useTasksStore = create<TasksState>((set, get) => ({
  tasks: [],
  meta: null,
  isLoading: false,
  error: null,
  currentPage: 1,
  isInitialized: false,

  fetchTasks: async (page = 1) => {
    if (get().isLoading && page === get().currentPage) {
      console.log('Already loading page:', page);
      return;
    }
    if (page === get().currentPage && get().tasks.length > 0) {
      console.log('Skip fetching: same page');
      return;
    }

    try {
      set({ isLoading: true, error: null });
      console.log('Fetching tasks for page:', page);

      const response = await TasksAPI.getTasks(page);

      set((prevState) => ({
        ...prevState,
        tasks: response.data,
        meta: response.meta,
        currentPage: page,
        isLoading: false,
        isInitialized: true,
        error: null,
      }));
    } catch (error: any) {
      console.error('Error fetching tasks:', error);
      set((prevState) => ({
        ...prevState,
        error: error.message || 'Failed to fetch tasks',
        isLoading: false,
        isInitialized: true,
      }));
    }
  },

  createTask: async (data: CreateTaskDto) => {
    try {
      set({ isLoading: true, error: null });

      await TasksAPI.createTask(data);

      const state = get();
      if (state.isInitialized) {
        await state.fetchTasks(state.currentPage);
      }

      set({ isLoading: false });
    } catch (error: any) {
      set({
        error: error.message || 'Failed to create task',
        isLoading: false
      });
      throw error;
    }
  }
}));