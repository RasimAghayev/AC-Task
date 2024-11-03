import { api } from '@/shared/lib/api';
import { CreateTaskDto, TasksResponse } from '@features/tasks/types/task.types.ts';

export const TasksAPI = {
  getTasks: async (page: number = 1) => {
    const response = await api.get<{
      error: null;
      result: TasksResponse[];
    }>(`/tasks?page=${page}`);
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