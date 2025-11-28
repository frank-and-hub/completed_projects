'use client';

import { TableView } from '@/components/table/TableView';
import { useState } from 'react';
import UseFilter from './filter';

export default function TasksPage() {
  const [filters, setFilters] = useState<Record<string, any>>({});
  const [showFilters, setShowFilters] = useState(false);
  
  return (
    <>
      <UseFilter setFilters={setFilters} visible={showFilters} />
      <TableView
        resource={`task`}
        addUrl={`/admin/tasks/add`}
        editUrl={(id) => `/admin/tasks/${id}/edit`}
        viewUrl={(id) => `/admin/tasks/${id}`}
        canDelete={true}
        columns={[
          { key: 'id', label: 'ID' },
          { key: 'title', label: 'Title' },
          { key: 'description', label: 'Description' },
          { key: 'assignedTo', label: 'Assigned To' },
          { key: 'assignedBy', label: 'Assigned By' },
          { key: 'priority', label: 'Priority' },
          { key: 'status', label: 'Status' },
          { key: 'dueDate', label: 'Due Date' },
        ]}
        filters={[
          filters,
          setFilters,
          showFilters,
          setShowFilters,
        ]}
      />
    </>
  );
}
