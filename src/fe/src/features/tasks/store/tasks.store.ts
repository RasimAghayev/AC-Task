import { create } from 'zustand';
import {
  Task,
  TaskFilters,
  TasksResponse,
  CreateTaskDto
} from '@features/tasks/types';
import { TasksAPI } from '@features/tasks/api/tasks.api.ts';
import { debounce } from '@/shared/utils/debounce';

interface TasksState {
  tasks: Task[];
  meta: TasksResponse['meta'] | null;
  isLoading: boolean;
  loadingTaskIds: number[];
  isCreating: boolean;
  isDeletingIds: number[];
  isSearching: boolean;
  error: string | null;
  currentPage: number;
  searchQuery: string;
  initialized: boolean;
  filters: TaskFilters;

  // Actions
  setSearchQuery: (query: string) => void;
  setFilters: (filters: TaskFilters) => void;
  setTaskLoading: (taskId: number, loading: boolean) => void;
  setTaskDeleting: (taskId: number, deleting: boolean) => void;
  resetFilters: () => void;
  fetchTasks: (page?: number, newFilters?: TaskFilters) => Promise<void>;
  createTask: (data: CreateTaskDto) => Promise<void>;
  updateTask: (id: number, data: CreateTaskDto) => Promise<void>;
  deleteTask: (id: number) => Promise<void>;
  debouncedSearch: (query: string) => void;

  // Computed
  hasActiveFilters: boolean;
}

const DEBOUNCE_MS = 500;

export const useTasksStore = create<TasksState>((set, get) => ({
  tasks: [],
  meta: null,
  isLoading: false,
  isSearching: false,
  loadingTaskIds: [],
  isCreating: false,
  isDeletingIds: [],
  error: null,
  currentPage: 1,
  searchQuery: '',
  initialized: false,
  filters: {},
  hasActiveFilters: false,

  setSearchQuery: (query: string) => {
    if (get().searchQuery === query) return;

    set({ searchQuery: query, isSearching: true });
    get().debouncedSearch(query);
  },

  setFilters: (newFilters: TaskFilters) => {
    const state = get();
    if (JSON.stringify(state.filters) === JSON.stringify(newFilters)) {
      return;
    }
    const cleanedFilters = Object.entries(newFilters).reduce((acc, [key, value]) => {
      if (value && Object.keys(value).length > 0) {
        acc[key] = value;
      }
      return acc;
    }, {} as TaskFilters);


    set({
      filters: cleanedFilters,
      hasActiveFilters: Object.keys(cleanedFilters).length > 0,
      currentPage: 1
    });

    get().fetchTasks(1, cleanedFilters);
  },

  resetFilters: () => {
    set({
      filters: {},
      hasActiveFilters: false,
      currentPage: 1,
      searchQuery: '',
      isSearching: false
    });
    get().fetchTasks(1);
  },

  setTaskLoading: (taskId: number, loading: boolean) => {
    set(state => ({
      loadingTaskIds: loading
        ? [...state.loadingTaskIds, taskId]
        : state.loadingTaskIds.filter(id => id !== taskId)
    }));
  },
  setTaskDeleting: (taskId: number, deleting: boolean) => {
    set(state => ({
      isDeletingIds: deleting
        ? [...state.isDeletingIds, taskId]
        : state.isDeletingIds.filter(id => id !== taskId)
    }));
  },
  debouncedSearch: debounce(() => {
    const state = get();
    if (!state.isLoading) {
      get().fetchTasks(1);
    }
  }, DEBOUNCE_MS),

  fetchTasks: async (page = 1, newFilters?: TaskFilters) => {
    const state = get();
    if (state.isLoading) {
      return;
    }
    const filters = newFilters || state.filters;

    try {
      set({ isLoading: true, error: null });

      const response = await TasksAPI.getTasks({
        page,
        filters: newFilters || state.filters,
        search: state.searchQuery
      });

      if (!response?.result?.data) {
        throw new Error('Invalid response format');
      }

      set({
        tasks: response.result.data,
        meta: response.result.meta || null,
        currentPage: page,
        isLoading: false,
        isSearching: false,
        error: null,
        initialized: true,
      });
    } catch (error: any) {
      set({
        tasks: [],
        meta: null,
        error: error.message || 'Failed to fetch tasks',
        isLoading: false,
        isSearching: false,
        initialized: true,
      });
    }
  },

  updateTask: async (id: number, data: CreateTaskDto) => {
    try {
      get().setTaskLoading(id, true);
      const response = await TasksAPI.updateTask(id, data);
      set(state => ({
        tasks: state.tasks.map(task =>
          task.id === id ? response.result.data : task
        )
      }));
      await get().fetchTasks(get().currentPage);
    } catch (error: any) {
      set({ error: error.message });
      throw error;
    } finally {
      get().setTaskLoading(id, false);
    }
  },

  deleteTask: async (id: number) => {
    try {
      get().setTaskDeleting(id, true);
      await TasksAPI.deleteTask(id);
      set(state => ({
        tasks: state.tasks.filter(task => task.id !== id),
        error: null
      }));
      const state = get();
      if (state.tasks.length === 0 && state.currentPage > 1) {
        await get().fetchTasks(state.currentPage - 1);
      } else {
        await get().fetchTasks(state.currentPage);
      }

    } catch (error: any) {
      set({ error: error.message });
      throw error;
    } finally {
      get().setTaskDeleting(id, false);
    }
  },

  createTask: async (data: CreateTaskDto): Promise<void> => {
    try {
      set({ isCreating: true, error: null });
      await TasksAPI.createTask(data);
      set({ error: null });
      await get().fetchTasks(1);
    } catch (error: any) {
      set({ error: error.message });
      // Not re-throwing, error is stored in state
    } finally {
      set({ isCreating: false });
    }
  }
}));