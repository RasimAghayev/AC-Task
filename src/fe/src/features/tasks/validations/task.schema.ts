import { z } from 'zod';
import { TaskStatus, TaskPriority, TaskRepeatType } from '@features/tasks/types/task.types.ts';

const baseSchema = z.object({
  title: z.string().min(3, 'Title must be at least 3 characters'),
  description: z.string().min(10, 'Description must be at least 10 characters'),
  dueDate: z.string().min(1, 'Due date is required'),
  status: z.nativeEnum(TaskStatus),
  priority: z.nativeEnum(TaskPriority),
  repeatType: z.nativeEnum(TaskRepeatType),
  repeatEndDate: z.string().optional().nullable(),
  tags: z.array(z.string()).min(1, 'At least one tag is required')
});

export const taskSchema = baseSchema.refine(
  (data) => {
    // If repeat type is custom, require repeat end date
    if (data.repeatType === TaskRepeatType.CUSTOM) {
      if (!data.repeatEndDate) return false;

      const endDate = new Date(data.repeatEndDate);
      const dueDate = new Date(data.dueDate);

      // End date should be after due date
      return endDate > dueDate;
    }

    return true;
  },
  {
    message: "End date is required and must be after due date when repeat type is custom",
    path: ["repeatEndDate"]
  }
);

export type CreateTaskSchema = z.infer<typeof taskSchema>;