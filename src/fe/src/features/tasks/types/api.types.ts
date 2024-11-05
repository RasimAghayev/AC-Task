import { TaskFilters } from './filter.types';

export interface APIResponse<T> {
  timestamp: string;
  path: string;
  method: string;
  error: null | string;
  result:  T | T[];
}

export interface TasksParams {
  page?: number;
  filters?: TaskFilters;
  search?: string;
}