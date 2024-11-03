import React, { useState } from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { X } from 'lucide-react';
import { Button } from '@/shared/components/ui/button';
import { Input } from '@/shared/components/ui/input';
import {
  Form,
  FormControl,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from '@/shared/components/ui/form';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/shared/components/ui/select';
import { CreateTaskSchema, taskSchema } from '@features/tasks/validations/task.schema.ts';
import { TaskPriority, TaskRepeatType, TaskStatus } from '@features/tasks/types/task.types.ts';
import { useTasksStore } from '@features/tasks/store/tasks.store.ts';

// Form type from schema
type FormData = CreateTaskSchema;
const statusOptions = [
  { value: TaskStatus.PENDING, label: 'Gözləyir', color: 'gray' },
  { value: TaskStatus.IN_PROGRESS, label: 'İcra edilir', color: 'blue' },
  { value: TaskStatus.UNDER_REVIEW, label: 'Yoxlanılır', color: 'yellow' },
  { value: TaskStatus.COMPLETED, label: 'Tamamlandı', color: 'green' },
  { value: TaskStatus.CANCELLED, label: 'Ləğv edildi', color: 'red' },
];

const priorityOptions = [
  { value: TaskPriority.LOW, label: 'Aşağı', color: '#8BB9DD' },
  { value: TaskPriority.MEDIUM, label: 'Orta', color: '#FFB347' },
  { value: TaskPriority.HIGH, label: 'Yüksək', color: '#FF6B6B' },
  { value: TaskPriority.URGENT, label: 'Təcili', color: '#DC3545' },
];

const repeatTypeOptions = [
  { value: TaskRepeatType.DAILY, label: 'Günlük', color: '#4CAF50' },
  { value: TaskRepeatType.WEEKLY, label: 'Həftəlik', color: '#2196F3' },
  { value: TaskRepeatType.HALF_A_MONTH, label: 'Yarım aylıq', color: '#673AB7' },
  { value: TaskRepeatType.MONTHLY, label: 'Aylıq', color: '#FF9800' },
  { value: TaskRepeatType.YEARLY, label: 'İllik', color: '#795548' },
  { value: TaskRepeatType.CUSTOM, label: 'Xüsusi', color: '#607D8B' },
];

interface CreateTaskFormProps {
  onClose: () => void;
}

export const CreateTaskForm = ({ onClose }: CreateTaskFormProps) => {
  const [tags, setTags] = useState<string[]>([]);
  const [newTag, setNewTag] = useState('');
  const { createTask, isLoading } = useTasksStore();

  const form = useForm<CreateTaskSchema>({
    resolver: zodResolver(taskSchema),
    defaultValues: {
      title: '',
      description: '',
      dueDate: new Date().toISOString().split('T')[0],
      status: TaskStatus.PENDING,
      priority: TaskPriority.MEDIUM,
      repeatType: TaskRepeatType.WEEKLY,
      repeatEndDate: '',
      tags: [],
    }
  });

  // Watch repeat type for conditional rendering
  const selectedRepeatType = form.watch('repeatType');
  React.useEffect(() => {
    if (selectedRepeatType !== TaskRepeatType.CUSTOM) {
      form.setValue('repeatEndDate', '');
    }
  }, [selectedRepeatType, form]);

  const addTag = () => {
    if (newTag.trim() && !tags.includes(newTag.trim())) {
      const updatedTags = [...tags, newTag.trim()];
      setTags(updatedTags);
      form.setValue('tags', updatedTags);
      setNewTag('');
    }
  };

  const removeTag = (tagToRemove: string) => {
    const updatedTags = tags.filter(tag => tag !== tagToRemove);
    setTags(updatedTags);
    form.setValue('tags', updatedTags);
  };

  const onSubmit = async (data: FormData) => {
    try {
      await createTask(data);
      onClose();
      form.reset();
    } catch (error) {
      console.error('Failed to create task:', error);
    }
  };

  return (
    <Form {...form}>
      <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-6">
        {/* Title */}
        <FormField
          control={form.control}
          name="title"
          render={({ field }) => (
            <FormItem>
              <FormLabel>Title *</FormLabel>
              <FormControl>
                <Input placeholder="Enter task title" {...field} />
              </FormControl>
              <FormMessage />
            </FormItem>
          )}
        />

        {/* Description */}
        <FormField
          control={form.control}
          name="description"
          render={({ field }) => (
            <FormItem>
              <FormLabel>Description *</FormLabel>
              <FormControl>
                <textarea
                  className="w-full min-h-[100px] p-2 border rounded-md resize-none focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
                  placeholder="Enter task description"
                  {...field}
                />
              </FormControl>
              <FormMessage />
            </FormItem>
          )}
        />

        {/* Grid for Date, Status, Priority and Repeat Type */}
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          {/* Due Date */}
          <FormField
            control={form.control}
            name="dueDate"
            render={({ field }) => (
              <FormItem>
                <FormLabel>Due Date *</FormLabel>
                <FormControl>
                  <Input
                    type="date"
                    min={new Date().toISOString().split('T')[0]}
                    {...field}
                  />
                </FormControl>
                <FormMessage />
              </FormItem>
            )}
          />

          {/* Status */}
          <FormField
            control={form.control}
            name="status"
            render={({ field }) => (
              <FormItem>
                <FormLabel>Status *</FormLabel>
                <Select onValueChange={field.onChange} defaultValue={field.value}>
                  <FormControl>
                    <SelectTrigger>
                      <SelectValue placeholder="Select status" />
                    </SelectTrigger>
                  </FormControl>
                  <SelectContent>
                    {statusOptions.map(status => (
                      <SelectItem key={status.value} value={status.value}>
                        <div className="flex items-center gap-2">
                          <div
                            className="w-2 h-2 rounded-full"
                            style={{ backgroundColor: status.color }}
                          />
                          {status.label}
                        </div>
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                <FormMessage />
              </FormItem>
            )}
          />

          {/* Priority */}
          <FormField
            control={form.control}
            name="priority"
            render={({ field }) => (
              <FormItem>
                <FormLabel>Priority *</FormLabel>
                <Select onValueChange={field.onChange} defaultValue={field.value}>
                  <FormControl>
                    <SelectTrigger>
                      <SelectValue placeholder="Select priority" />
                    </SelectTrigger>
                  </FormControl>
                  <SelectContent>
                    {priorityOptions.map(priority => (
                      <SelectItem key={priority.value} value={priority.value}>
                        <div className="flex items-center gap-2">
                          <div
                            className="w-2 h-2 rounded-full"
                            style={{ backgroundColor: priority.color }}
                          />
                          {priority.label}
                        </div>
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                <FormMessage />
              </FormItem>
            )}
          />

          {/* Repeat Type */}
          <FormField
            control={form.control}
            name="repeatType"
            render={({ field }) => (
              <FormItem>
                <FormLabel>Repeat Type *</FormLabel>
                <Select onValueChange={field.onChange} defaultValue={field.value}>
                  <FormControl>
                    <SelectTrigger>
                      <SelectValue placeholder="Select repeat type" />
                    </SelectTrigger>
                  </FormControl>
                  <SelectContent>
                    {repeatTypeOptions.map(type => (
                      <SelectItem key={type.value} value={type.value}>
                        <div className="flex items-center gap-2">
                          <div
                            className="w-2 h-2 rounded-full"
                            style={{ backgroundColor: type.color }}
                          />
                          {type.label}
                        </div>
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                <FormMessage />
              </FormItem>
            )}
          />

          {/* Repeat End Date - only show when Custom is selected */}
          {selectedRepeatType === TaskRepeatType.CUSTOM && (
            <FormField
              control={form.control}
              name="repeatEndDate"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Repeat End Date *</FormLabel>
                  <FormControl>
                    <Input
                      type="date"
                      min={new Date().toISOString().split('T')[0]}
                      {...field}
                      value={''}
                    />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />
          )}
        </div>

        {/* Tags */}
        <div className="space-y-2">
          <FormLabel>Tags *</FormLabel>
          <div className="flex gap-2">
            <Input
              value={newTag}
              onChange={(e) => setNewTag(e.target.value)}
              onKeyPress={(e) => e.key === 'Enter' && (e.preventDefault(), addTag())}
              placeholder="Add a tag"
            />
            <Button
              type="button"
              onClick={addTag}
              size="default"
            >
              Add
            </Button>
          </div>
          {tags.length > 0 && (
            <div className="flex flex-wrap gap-2 mt-2">
              {tags.map((tag) => (
                <div
                  key={tag}
                  className="flex items-center gap-1 px-2 py-1 bg-gray-100 rounded-full text-sm"
                >
                  {tag}
                  <button
                    type="button"
                    onClick={() => removeTag(tag)}
                    className="text-gray-500 hover:text-red-500"
                  >
                    <X size={14} />
                  </button>
                </div>
              ))}
            </div>
          )}
          {form.formState.errors.tags && (
            <p className="text-sm text-red-500">
              {form.formState.errors.tags.message}
            </p>
          )}
        </div>

        {/* Action Buttons */}
        <div className="flex justify-end gap-2 pt-4 border-t">
          <Button
            type="button"
            variant="outline"
            onClick={onClose}
            disabled={isLoading}
          >
            Cancel
          </Button>
          <Button
            type="submit"
            disabled={isLoading}
          >
            {isLoading ? 'Creating...' : 'Create Task'}
          </Button>
        </div>
      </form>
    </Form>
  );
};