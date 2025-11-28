'use client';

import { TableView } from '@/components/table/TableView';
import { useState } from 'react';
import UseFilter from './filter';

export default function BusinessPage() {
  const [filters, setFilters] = useState<Record<string, any>>({});
  const [showFilters, setShowFilters] = useState(false);
  
  return (
    <>
      <UseFilter setFilters={setFilters} visible={showFilters} />
      <TableView
        resource={`business`}
        addUrl={`/admin/business/add`}
        editUrl={(id) => `/admin/business/${id}/edit`}
        viewUrl={(id) => `/admin/business/${id}`}
        canDelete={true}
        columns={[
          { key: 'id', label: 'ID' },
          { key: 'name', label: 'Business Name' },
          { key: 'email', label: 'Email' },
          { key: 'phone', label: 'Phone' },
          { key: 'address', label: 'Address' },
          { key: 'status', label: 'Status' },
          { key: 'createdAt', label: 'Created At' },
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