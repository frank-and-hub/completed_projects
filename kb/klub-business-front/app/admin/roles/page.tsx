'use client';

import { TableView } from '@/components/table/TableView';
import { useState } from 'react';
import UseFilter from './filter';

export default function RolesPage() {
  const [filters, setFilters] = useState<Record<string, any>>({});
  const [showFilters, setShowFilters] = useState(false);
  return (
    <>
     <UseFilter setFilters={setFilters} visible={showFilters} />
      <TableView
        resource={`roles`}
        addUrl={`/admin/roles/add`}
        editUrl={(id) => `/admin/roles/${id}/edit`}
        viewUrl={(id) => `/admin/roles/${id}`}
        canDelete={true}
        columns={[
          { key: 'id', label: 'ID' },
          { key: 'name', label: 'Name' },
          { key: 'description', label: 'Description' },
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
