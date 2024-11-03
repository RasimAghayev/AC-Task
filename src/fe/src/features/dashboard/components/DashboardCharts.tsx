import React, { useEffect } from 'react';
import {
  PieChart,
  Pie,
  Cell,
  ResponsiveContainer,
  Tooltip,
  Legend
} from 'recharts';
import { useDashboardStore } from '@features/dashboard';
import { useAuth } from '@/features/auth/hooks/useAuth';

interface ChartData {
  name: string;
  value: number;
  color: string;
}

interface CustomLabelProps {
  cx: number;
  cy: number;
  midAngle: number;
  innerRadius: number;
  outerRadius: number;
  percent: number;
}

const RADIAN = Math.PI / 180;
const renderCustomizedLabel = ({
                                 cx,
                                 cy,
                                 midAngle,
                                 innerRadius,
                                 outerRadius,
                                 percent
                               }: CustomLabelProps) => {
  const radius = innerRadius + (outerRadius - innerRadius) * 0.5;
  const x = cx + radius * Math.cos(-midAngle * RADIAN);
  const y = cy + radius * Math.sin(-midAngle * RADIAN);

  return (
    <text
      x={x}
      y={y}
      fill="white"
      textAnchor={x > cx ? 'start' : 'end'}
      dominantBaseline="central"
      className="text-xs"
    >
      {`${(percent * 100).toFixed(0)}%`}
    </text>
  );
};

interface CustomTooltipProps {
  active?: boolean;
  payload?: Array<{
    payload: ChartData;
  }>;
  totalTasks: number;
}

const CustomTooltip = ({ active, payload, totalTasks }: CustomTooltipProps) => {
  if (active && payload && payload.length) {
    const data = payload[0].payload;
    return (
      <div className="bg-white p-2 shadow-lg rounded border">
        <p className="font-semibold">{data.name}</p>
        <p className="text-gray-600">Count: {data.value}</p>
        <p className="text-gray-600">
          Percentage: {((data.value / totalTasks) * 100).toFixed(1)}%
        </p>
      </div>
    );
  }
  return null;
};

export const DashboardCharts = () => {
  const { isAuthenticated } = useAuth();
  const { reports, isLoading, error, fetchReports } = useDashboardStore();

  useEffect(() => {
    if (isAuthenticated) {
      console.log('Fetching reports from DashboardCharts');
      fetchReports();
    }
  }, [isAuthenticated]);

  useEffect(() => {
    console.log('DashboardCharts mounted');
    fetchReports();
  }, [fetchReports]);

  useEffect(() => {
    console.log('Current reports state:', reports);
  }, [reports]);

  if (isLoading) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div>
        <span className="ml-2">Loading reports...</span>
      </div>
    );
  }

  if (error) {
    return (
      <div className="text-red-500 p-4 bg-red-50 rounded-lg">
        Error: {error}
      </div>
    );
  }

  if (error) {
    return (
      <div className="text-red-500 p-4 bg-red-50 rounded-lg">
        <p>Error loading reports: {error}</p>
        <button
          onClick={() => fetchReports()}
          className="mt-2 px-4 py-2 bg-red-100 rounded hover:bg-red-200"
        >
          Retry
        </button>
      </div>
    );
  }
  if (!reports) {
    return (
      <div className="text-gray-500 p-4 bg-gray-50 rounded-lg">
        <p>No data available</p>
        <button
          onClick={() => fetchReports()}
          className="mt-2 px-4 py-2 bg-gray-100 rounded hover:bg-gray-200"
        >
          Load Data
        </button>
      </div>
    );
  }

  const statusData: ChartData[] = Object.values(reports.by_status)
    .map(value => ({
      name: value.label,
      value: value.count,
      color: value.color
    }))
    .filter(item => item.value > 0);

  const priorityData: ChartData[] = Object.values(reports.by_priority)
    .map(value => ({
      name: value.label,
      value: value.count,
      color: value.color
    }))
    .filter(item => item.value > 0);

  const repeatTypeData: ChartData[] = Object.values(reports.by_repeat_type)
    .map(value => ({
      name: value.label,
      value: value.count,
      color: value.color
    }))
    .filter(item => item.value > 0);

  // @ts-ignore
  return (
    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-6">
      {/* Status Chart */}
      <div className="bg-white p-6 rounded-xl shadow-lg">
        <h3 className="text-lg font-semibold mb-6 text-gray-800">
          Tasks by Status
        </h3>
        <div className="h-80">
          <ResponsiveContainer width="100%" height="100%">
            <PieChart>
              <Pie
                data={statusData}
                dataKey="value"
                nameKey="name"
                cx="50%"
                cy="50%"
                innerRadius={60}
                outerRadius={80}
                label={renderCustomizedLabel}
                labelLine={false}
              >
                {statusData.map((entry, index) => (
                  <Cell
                    key={`cell-${index}`}
                    fill={entry.color}
                    strokeWidth={2}
                  />
                ))}
              </Pie>
              <Tooltip content={(props) =>
                <CustomTooltip {...props} totalTasks={reports.total_tasks} />
              } />
              <Legend
                layout="vertical"
                align="right"
                verticalAlign="middle"
                iconType="circle"
              />
            </PieChart>
          </ResponsiveContainer>
        </div>
      </div>

      {/* Priority Chart */}
      <div className="bg-white p-6 rounded-xl shadow-lg">
        <h3 className="text-lg font-semibold mb-6 text-gray-800">
          Tasks by Priority
        </h3>
        <div className="h-80">
          <ResponsiveContainer width="100%" height="100%">
            <PieChart>
              <Pie
                data={priorityData}
                dataKey="value"
                nameKey="name"
                cx="50%"
                cy="50%"
                innerRadius={60}
                outerRadius={80}
                label={renderCustomizedLabel}
                labelLine={false}
              >
                {priorityData.map((entry, index) => (
                  <Cell
                    key={`cell-${index}`}
                    fill={entry.color}
                    strokeWidth={2}
                  />
                ))}
              </Pie>
              <Tooltip content={(props) =>
                <CustomTooltip {...props} totalTasks={reports.total_tasks} />
              } />
              <Legend
                layout="vertical"
                align="right"
                verticalAlign="middle"
                iconType="circle"
              />
            </PieChart>
          </ResponsiveContainer>
        </div>
      </div>

      {/* Repeat Type Chart */}
      <div className="bg-white p-6 rounded-xl shadow-lg">
        <h3 className="text-lg font-semibold mb-6 text-gray-800">
          Tasks by Repeat Type
        </h3>
        <div className="h-80">
          <ResponsiveContainer width="100%" height="100%">
            <PieChart>
              <Pie
                data={repeatTypeData}
                dataKey="value"
                nameKey="name"
                cx="50%"
                cy="50%"
                innerRadius={60}
                outerRadius={80}
                label={renderCustomizedLabel}
                labelLine={false}
              >
                {repeatTypeData.map((entry, index) => (
                  <Cell
                    key={`cell-${index}`}
                    fill={entry.color}
                    strokeWidth={2}
                  />
                ))}
              </Pie>
              <Tooltip content={(props) =>
                <CustomTooltip {...props} totalTasks={reports.total_tasks} />
              } />
              <Legend
                layout="vertical"
                align="right"
                verticalAlign="middle"
                iconType="circle"
              />
            </PieChart>
          </ResponsiveContainer>
        </div>
      </div>
    </div>
  );
};