import { TaskStatus, TaskPriority, TaskRepeatType } from './task.types';

export type ComparisonOperator = 'eq' | 'lt' | 'lte' | 'gt' | 'gte';
export type ArrayOperator = 'in' | 'bt';
export type StringOperator = 'lk' | 'nlk';
export type JsonOperator = 'json';

export type FilterOperator = ComparisonOperator | ArrayOperator | StringOperator | JsonOperator;

export interface TaskFilters {
  status?: { [key in ComparisonOperator | ArrayOperator]?: TaskStatus | TaskStatus[] };
  priority?: { [key in ComparisonOperator | ArrayOperator]?: TaskPriority | TaskPriority[] };
  repeat_type?: { [key in ComparisonOperator | ArrayOperator]?: TaskRepeatType | TaskRepeatType[] };
  due_date?: { [key in ComparisonOperator | ArrayOperator]?: string | [string, string] };
  repeat_end_date?: { [key in ComparisonOperator | ArrayOperator]?: string | [string, string] };
  title?: { [key in StringOperator | 'eq']?: string };
  description?: { [key in StringOperator]?: string };
  tags?: { [key in JsonOperator]?: string[] };
  created_at?: { [key in ComparisonOperator | ArrayOperator]?: string | [string, string] };
  updated_at?: { [key in ComparisonOperator | ArrayOperator]?: string | [string, string] };
}