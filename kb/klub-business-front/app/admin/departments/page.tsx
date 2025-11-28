'use client';

import { TableView } from '@/components/table/TableView';
import { useState } from 'react';
import UseFilter from './filter';

export default function DepartmentsPage() {
  const [filters, setFilters] = useState<Record<string, any>>({});
  const [showFilters, setShowFilters] = useState(false);
  
  return (
    <>
      <UseFilter setFilters={setFilters} visible={showFilters} />
      <TableView
        resource={`departments`}
        addUrl={`/admin/departments/add`}
        editUrl={(id) => `/admin/departments/${id}/edit`}
        viewUrl={(id) => `/admin/departments/${id}`}
        canDelete={true}
        columns={[
          { key: 'id', label: 'ID' },
          { key: 'name', label: 'Name' },
          { key: 'description', label: 'Description' },
          { key: 'head', label: 'Department Head' },
          { key: 'employeeCount', label: 'Employees' },
          { key: 'status', label: 'Status' },
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
