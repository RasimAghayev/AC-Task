import { useEffect } from 'react';
import { DashboardCharts, useDashboardStore } from '@features/dashboard';

export const DashboardPage = () => {
  const { reports, error, isLoading } = useDashboardStore();

  useEffect(() => {
    console.log('DashboardPage mounted');
  }, []);

  return (
    <div className="container mx-auto py-6">
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-2xl font-bold">Dashboard</h1>

        <div className="text-sm text-gray-500">
          {isLoading && 'Loading...'}
          {error && <span className="text-red-500">Error: {error}</span>}
          {reports && (
            <span>Total Tasks: {reports.total_tasks}</span>
          )}
        </div>
      </div>

      <DashboardCharts />
    </div>
  );
};