import { api } from '@/shared/lib/api';
import type {
  CreateTaskDto,
  TasksResponse,
  Task,
  TaskFilters,
  APIResponse,
  TasksParams
} from '@features/tasks/types';

const createURLSearchParams = (
  page: number,
  filters?: TaskFilters,
  search?: string
): URLSearchParams => {
  const params = new URLSearchParams();

  params.append('page', page.toString());

  if (search) {
    params.append('search', search);
  }

  if (filters) {
    Object.entries(filters).forEach(([field, operators]) => {
      Object.entries(operators).forEach(([operator, value]) => {
        if (Array.isArray(value)) {
          if (operator === 'bt') {
            params.append(`${field}[${operator}]`, value.join(','));
          } else {
            value.forEach(v => params.append(`${field}[${operator}][]`, v.toString()));
          }
        } else if (value instanceof Date) {
          params.append(`${field}[${operator}]`, value.toISOString().split('T')[0]);
        } else {
          params.append(`${field}[${operator}]`, value!.toString());
        }
      });
    });
  }

  return params;
};

export const TasksAPI = {
  getTasks: async (params: TasksParams): Promise<TasksResponse> => {
    const searchParams = createURLSearchParams(
      params.page || 1,
      params.filters,
      params.search
    );

    try {
      const response = await api.get<APIResponse<TasksResponse>>(
        `/tasks?${searchParams.toString()}`
      );
      console.log('Raw API response:', response.data);

      if (!response.data || !response.data.result || !Array.isArray(response.data.result)) {
        throw new Error('Invalid response format');
      }

      const responseData = response.data;

      // If response.data.result is not an array, wrap it
      const result = Array.isArray(responseData.result)
        ? responseData.result
        : [responseData.result];

      return {
        ...responseData,
        result: result
      };
      } catch (error: any) {
        console.error('API Error:', error);
        throw new Error(error.message || 'Failed to fetch tasks');
      }
    },


  deleteTask: async (id: number) => {
    const response = await api.delete<APIResponse<void>>(`/tasks/${id}`);
    return response.data;
  },
  updateTask: async (id: number, data: CreateTaskDto) => {
    const response = await api.patch<APIResponse<Task>>(`/tasks/${id}`, data);
    return response.data;
  },
  createTask: async (data: CreateTaskDto): Promise<Task> => {
    const response = await api.post<APIResponse<{
      message: string;
      data: Task;
    }>>('/tasks', data);

    if (response.data.error) {
      throw new Error(response.data.error);
    }

    return response.data.result.data;
  }
};