'use client';

import { TableView } from '@/components/table/TableView';
import UseFilter from './filter';
import { useState } from 'react';

export default function UsersPage() {
  const [filters, setFilters] = useState<Record<string, any>>({});
  const [showFilters, setShowFilters] = useState(false);
  return (
    <>
      <UseFilter setFilters={setFilters} visible={showFilters} />
      <TableView
        resource={`users`}
        addUrl={``}
        editUrl={(id) => `/admin/users/${id}/edit`}
        viewUrl={(id) => `/admin/users/${id}`}
        canDelete={true}
        columns={[
          { key: 'id', label: 'ID' },
          { key: 'firstName', label: 'First Name' },
          { key: 'email', label: 'Email' },
          { key: `phone`, label: `Phone Number` },
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
