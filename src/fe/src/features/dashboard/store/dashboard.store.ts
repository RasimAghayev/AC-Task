import { create } from 'zustand';
import { TaskAPI, TaskReportData } from '@features/dashboard';

interface DashboardState {
  reports: TaskReportData | null;
  isLoading: boolean;
  error: string | null;
  fetchReports: () => Promise<void>;
}

export const useDashboardStore = create<DashboardState>((set, get) => ({
  reports: null,
  isLoading: false,
  error: null,

  fetchReports: async () => {
    if (get().isLoading) {
      console.log('Already fetching reports, skipping...');
      return;
    }


    try {
      set({ isLoading: true, error: null });

      console.log('Fetching reports...');
      const response = await TaskAPI.getReports();
      console.log('Reports received:', response);

      set({
        reports: response.data,
        isLoading: false,
        error: null
      });
    } catch (error: any) {
      console.error('Error fetching reports:', error);
      set({
        reports: null,
        error: error.message || 'Failed to fetch reports',
        isLoading: false
      });
    }
  }
}));