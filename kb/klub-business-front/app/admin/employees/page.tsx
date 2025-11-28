'use client';

import { TableView } from '@/components/table/TableView';
import { useState } from 'react';
import UseFilter from './filter';

export default function EmployeesPage() {
  const [filters, setFilters] = useState<Record<string, any>>({});
  const [showFilters, setShowFilters] = useState(false);
  
  return (
    <>
      <UseFilter setFilters={setFilters} visible={showFilters} />
      <TableView
        resource={`employee`}
        addUrl={`/admin/employees/add`}
        editUrl={(id) => `/admin/employees/${id}/edit`}
        viewUrl={(id) => `/admin/employees/${id}`}
        canDelete={true}
        columns={[
          { key: 'id', label: 'ID' },
          { key: 'firstName', label: 'First Name' },
          { key: 'lastName', label: 'Last Name' },
          { key: 'email', label: 'Email' },
          { key: 'phone', label: 'Phone' },
          { key: 'position', label: 'Position' },
          { key: 'department', label: 'Department' },
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
