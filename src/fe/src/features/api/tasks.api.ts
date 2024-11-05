import { api } from '@/shared/lib/api';
import {
  CreateTaskDto,
  TasksResponse,
  TasksFilterParams,
  APIResponse
} from '@features/tasks/types/task.types.ts';


const transformFilters = (filters?: TasksFilterParams): Record<string, string> => {
  if (!filters) return {};

  const params: Record<string, string> = {};

  Object.entries(filters).forEach(([field, conditions]) => {
    Object.entries(conditions).forEach(([operator, value]) => {
      if (Array.isArray(value)) {
        if (operator === 'bt') {
          params[`filter[${field}][${operator}]`] = value.join(',');
        } else if (operator === 'in') {
          params[`filter[${field}][${operator}]`] = value.join(',');
        }
      } else if (field === 'tags') {
        params[`filter[${field}][${operator}]`] = JSON.stringify(value);
      } else {
        params[`filter[${field}][${operator}]`] = String(value);
      }
    });
  });

  return params;
};
export const TasksAPI = {
  getTasks: async (page: number = 1, filters?: TasksFilterParams) => {
    const baseParams = { page: page.toString() };
    const filterParams = transformFilters(filters);

    const searchParams = new URLSearchParams({
      ...baseParams,
      ...filterParams
    });

    const response = await api.get<APIResponse<TasksResponse[]>>(
      `/tasks?${searchParams.toString()}`
    );

    if (response.data.error) {
      throw new Error(response.data.error);
    }

    return response.data.result[0];
  },
  createTask: async (data: CreateTaskDto) => {
    const response = await api.post<{
      error: null;
      result: {
        message: string;
        data: any;
      };
    }>('/tasks', data);

    if (response.data.error) {
      throw new Error(response.data.error);
    }

    return response.data.result;
  }
};