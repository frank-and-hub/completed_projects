"use client";
import { Loader, Table, Title } from "@mantine/core";
import React from "react";
import useTransactionHistory from "./useTransactionHistory";
import dayjs from "dayjs";

function TransactionHistoryTable() {
  const { data, isLoading } = useTransactionHistory();

  const rows = data?.data?.map((element) => (
    <Table.Tr key={element?.amount}>
      <Table.Td>{dayjs(element?.purchase_date).format("DD/MM/YYYY")}</Table.Td>
      <Table.Td>{element?.transcation_id}</Table.Td>
      <Table.Td>{`${element?.started_at}-${element?.expired_at}`}</Table.Td>
      <Table.Td style={{ color: "#F30051" }}>{element?.plan_type}</Table.Td>
      <Table.Td>{element?.amount}</Table.Td>
      <Table.Td style={{ color: "#F30051" }}>
        <Title order={5}> {element?.no_of_requests || "-"}</Title>
      </Table.Td>
    </Table.Tr>
  ));

  return (
    <Table striped withRowBorders={false} verticalSpacing="lg">
      <Table.Thead>
        <Table.Tr>
          <Table.Th>PURCHASE DATE</Table.Th>
          <Table.Th>Transaction ID</Table.Th>
          <Table.Th>Active Period</Table.Th>
          <Table.Th>Plan Type</Table.Th>
          <Table.Th>
            Amount Paid <span>(Rand)</span>
          </Table.Th>
          <Table.Th>No. Of Requests</Table.Th>
        </Table.Tr>
      </Table.Thead>
      <Table.Tbody>
        {isLoading ? (
          <Loader
            size={20}
            style={{
              position: "absolute",
              top: "50%",
              left: "50%",
              transform: "translate(-50%, 0px)",
            }}
          />
        ) : data?.data?.length ? (
          rows
        ) : (
          <p
            style={{
              position: "absolute",
              top: "50%",
              left: "50%",
              transform: "translate(-50%, 0px)",
            }}
          >
            No data found
          </p>
        )}
      </Table.Tbody>
    </Table>
  );
}

export default TransactionHistoryTable;
