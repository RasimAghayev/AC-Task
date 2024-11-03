interface StatusData {
  count: number;
  label: string;
  color: string;
}

interface PriorityData {
  count: number;
  label: string;
  color: string;
}

interface RepeatTypeData {
  count: number;
  label: string;
  color: string;
}

export interface TaskReportData {
  total_tasks: number;
  completion_rate: number;
  by_status: {
    pending: StatusData;
    in_progress: StatusData;
    under_review: StatusData;
    completed: StatusData;
    cancelled: StatusData;
  };
  by_priority: {
    low: PriorityData;
    medium: PriorityData;
    high: PriorityData;
    urgent: PriorityData;
  };
  by_repeat_type: {
    daily: RepeatTypeData;
    weekly: RepeatTypeData;
    half_a_month: RepeatTypeData;
    monthly: RepeatTypeData;
    yearly: RepeatTypeData;
    custom: RepeatTypeData;
  };
  generated_at: string;
  user_id: number;
}

export interface TaskReport {
  message: string;
  data: TaskReportData;
}