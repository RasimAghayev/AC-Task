export enum TaskStatus {
  PENDING = 'pending',
  IN_PROGRESS = 'in_progress',
  UNDER_REVIEW = 'under_review',
  COMPLETED = 'completed',
  CANCELLED = 'cancelled'
}

export enum TaskPriority {
  LOW = 'low',
  MEDIUM = 'medium',
  HIGH = 'high',
  URGENT = 'urgent'
}

export enum TaskRepeatType {
  DAILY = 'daily',
  WEEKLY = 'weekly',
  HALF_A_MONTH = 'half_a_month',
  MONTHLY = 'monthly',
  YEARLY = 'yearly',
  CUSTOM = 'custom'
}

export interface Task {
  id: number;
  title: string;
  description: string;
  dueDate: string;
  status: {
    value: TaskStatus;
    label: string;
    color: string;
  };
  priority: {
    value: TaskPriority;
    label: string;
    color: string;
  };
  repeatType: {
    value: TaskRepeatType;
    label: string;
    color: string;
  };
  repeatEndDate: string | null;
  tags: string[];
  createdAt: string;
  updatedAt: string;
}

export interface CreateTaskDto {
  title: string;
  description: string;
  dueDate: string;
  status: TaskStatus;
  priority: TaskPriority;
  repeatType: TaskRepeatType;
  repeatEndDate?: string;
  tags: string[];
}

export interface TasksResponse {
  data: Task[];
  meta: {
    current_page: number;
    from: number;
    last_page: number;
    per_page: number;
    to: number;
    total: number;
  };
  links: {
    self: string;
    first: string;
    last: string;
    prev: string | null;
    next: string | null;
  };
}