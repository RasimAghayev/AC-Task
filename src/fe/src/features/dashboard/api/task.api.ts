import { api } from '@/shared/lib/api';
import { TaskReport } from '@features/dashboard';

export const TaskAPI = {
  getReports: async (): Promise<TaskReport> => {
    try {
      console.log('Making API request to /tasks/reports');
      const response = await api.get<{
        error: null;
        result: TaskReport;
      }>('/tasks/reports');

      console.log('API Response:', response.data);
      return response.data.result;
    } catch (error) {
      console.error('API Error:', error);
      throw error;
    }
  }
};