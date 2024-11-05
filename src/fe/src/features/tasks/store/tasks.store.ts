import { create } from 'zustand';
import { TasksAPI } from '../api/tasks.api';
import {
  Task,
  TaskFilters,
  TasksResponse,
  CreateTaskDto
} from '@features/tasks/types';

function debounce<T extends (...args: any[]) => any>(
  func: T,
  wait: number
): (...args: Parameters<T>) => void {
  let timeout: NodeJS.Timeout | null = null;

  return (...args: Parameters<T>) => {
    if (timeout) {
      clearTimeout(timeout);
    }

    timeout = setTimeout(() => {
      func(...args);
    }, wait);
  };
}

interface TasksState {
  tasks: Task[];
  meta: TasksResponse['meta'] | null;
  isLoading: boolean;
  isSearching: boolean;
  error: string | null;
  currentPage: number;
  searchQuery: string;
  initialized: boolean;
  filters: TaskFilters;

  // Actions
  setSearchQuery: (query: string) => void;
  setFilters: (filters: TaskFilters) => void;
  resetFilters: () => void;
  fetchTasks: (page?: number, newFilters?: TaskFilters) => Promise<void>;
  createTask: (data: CreateTaskDto) => Promise<void>;
  updateTask: (id: number, data: CreateTaskDto) => Promise<void>;
  deleteTask: (id: number) => Promise<void>;
  debouncedSearch: (query: string) => void;

  // Computed
  hasActiveFilters: boolean;
}

const initialState: {
  isLoading: boolean;
  hasActiveFilters: boolean;
  meta: null;
  searchQuery: string;
  initialized: boolean;
  isSearching: boolean;
  filters: {};
  error: null;
  currentPage: number;
  tasks: any[]
} = {
  tasks: [],
  meta: null,
  isLoading: false,
  isSearching: false,
  error: null,
  currentPage: 1,
  searchQuery: '',
  filters: {},
  hasActiveFilters: false,
  initialized: false
};

const DEBOUNCE_MS = 500;

export const useTasksStore = create<TasksState>((set, get) => ({
  ...initialState,
  tasks: [],
  meta: null,
  isLoading: false,
  isSearching: false,
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

    // Log the filters for debugging
    console.log('Applied filters:', cleanedFilters);

    setTimeout(() => {
      get().fetchTasks(1, cleanedFilters);
    }, 0);
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

  debouncedSearch: debounce((query: string) => {
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

      console.log('Fetching tasks with:', {
        page,
        filters,
        search: state.searchQuery
      });

      const response = await TasksAPI.getTasks({
        page,
        filters: newFilters || state.filters,
        search: state.searchQuery
      });

      console.log('API Response:', response);


      if (!response || !Array.isArray(response.result)) {
        throw new Error('Invalid response format');
      }

      const result = response.result[0];
      if (!result || !result.data) {
        set({
          tasks: [],
          meta: null,
          currentPage: page,
          isLoading: false,
          isSearching: false,
          error: null,
          initialized: true,
        });
        return;
      }
      set({
        tasks: Array.isArray(result.data) ? result.data : [],
        meta: result.meta || null,
        currentPage: page,
        isLoading: false,
        isSearching: false,
        error: null,
        initialized: true,
      });

      console.log('Tasks loaded:', result.data.length);
      console.log('Meta:', result.meta);
    } catch (error: any) {
      console.error('Error fetching tasks:', error);
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
      set({ isLoading: true, error: null });
      await TasksAPI.updateTask(id, data);
      await get().fetchTasks(get().currentPage);
    } catch (error: any) {
      set({ error: error.message, isLoading: false });
      throw error;
    }
  },

  deleteTask: async (id: number) => {
    try {
      set({ isLoading: true, error: null });
      await TasksAPI.deleteTask(id);
      await get().fetchTasks(get().currentPage);
    } catch (error: any) {
      set({ error: error.message, isLoading: false });
      throw error;
    }
  },

  createTask: async (data: CreateTaskDto) => {
    try {
      set({ isLoading: true, error: null });
      await TasksAPI.createTask(data);
      await get().fetchTasks(1);
      return true;
    } catch (error: any) {
      set({
        error: error.message,
        tasks: get().tasks,
        isLoading: false
      });
      throw error;
    }
  }
}));