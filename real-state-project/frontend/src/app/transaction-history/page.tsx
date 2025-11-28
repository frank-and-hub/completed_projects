import { Box, Container } from "@mantine/core";
import PropertyHeader from "../propertyNeeds/components/PropertyHeader";
import loginImage from "../../../assets/svg/history_img.svg";
import TransactionHistoryTable from "./TransactionHistoryTable";
import "./TranscationHistory.scss";

export function generateMetadata() {
  return {
    title: "PocketProperty | Your Transaction History",
    description:
      "View and manage all your previous transactions securely within your PocketProperty account.",
    robots: "noindex, nofollow, noarchive",
  };
}

function TransactionHistory() {
  return (
    <section>
      <PropertyHeader
        title="Transaction History"
        description="Review your transactions easily. Stay updated on your 
        account activity."
        image={loginImage}
      />

      <div
        className="transaction_history_list_sec"
        style={{ position: "relative", minHeight: 350 }}
      >
        <Container size={"lg"}>
          <Box className="table_responsive">
            <TransactionHistoryTable />
          </Box>
        </Container>
      </div>
    </section>
  );
}

export default TransactionHistory;
