import React, { useEffect, useState } from "react";
import { FaceLivenessDetector } from "@aws-amplify/ui-react-liveness";
import { Loader, ThemeProvider, Theme } from "@aws-amplify/ui-react";
import "@aws-amplify/ui-react/styles.css";
import { Box, Image, Button, Paper, Title, Text } from "@mantine/core";
import { getBaseURl } from "@/utils/createIconUrl";
import { checkValidSession, createAWSSession } from "@/api/auth/login";
import { Amplify } from "aws-amplify";
import awsexports from "../../aws-exports";

Amplify.configure(awsexports);

export function LivenessQuickStartReact({
  onCapture,
}: {
  onCapture: (imageSrc?: any) => void;
}) {
  const [loading, setLoading] = React.useState<boolean>(false);
  const [userImagePath, setUserImagePath] = useState<string>();
  const [isNotHuman, setIsNotHuman] = useState(false);
  const [createLivenessApiData, setCreateLivenessApiData] = React.useState<{
    sessionId: string;
  } | null>(
    // {
    // sessionId: "91f9ff5a-9709-4002-a1c9-83ff894a8381",
    // }
    null
  );
  const [startCheck, setStartCheck] = React.useState<boolean>(true); // New state to control check start

  useEffect(() => {
    fetchCreateLiveness();
  }, []);

  const fetchCreateLiveness = async () => {
    try {
      onCapture();
      setIsNotHuman(false);
      setCreateLivenessApiData(null);
      setUserImagePath(undefined);
      setLoading(true); // Set to true when the check starts

      const response = await createAWSSession();
      // const response = await axios
      //   .get("http://localhost:3001/liveness/create")
      //   .then((res) => res?.data);

      const mockResponse = { sessionId: response?.sessionId };
      const data = mockResponse;

      setCreateLivenessApiData(data);
      setStartCheck(false); // Set to true when the check starts
      setLoading(false);
    } catch (error) {
      setLoading(false); // Set to true when the check starts
    }
  };

  const handleAnalysisComplete: () => Promise<void> = async () => {
    if (!createLivenessApiData?.sessionId) {
      alert("Session ID is not available");
      return;
    }

    const response = await checkValidSession(createLivenessApiData);

    if (response) {
      console.log("User is live", response);
      if (response?.data?.confidence >= 50) {
        setUserImagePath(response?.path);
        setCreateLivenessApiData(null);
        setStartCheck(true);
        onCapture(response?.path);
      } else {
        setCreateLivenessApiData(null);
        setStartCheck(true);
        setIsNotHuman(true);
      }
    } else {
      console.log("User is not live");
    }
  };

  // Custom theme with primary color and font
  const customTheme: Theme = {
    name: "Custom Liveness Theme",
    tokens: {
      colors: {
        brand: {
          primary: {
            "10": { value: "#f30051" }, // Primary color
            "80": { value: "#cc0044" },
            "90": { value: "#b3003b" },
            "100": { value: "#990033" },
          },
        },
        background: {
          primary: { value: "#ffffff" },
        },
      },
      fonts: {
        default: {
          variable: { value: "Poppins, sans-serif" }, // Custom font
        },
      },
      components: {
        button: {
          primary: {
            backgroundColor: { value: "{colors.brand.primary.10}" },
            color: { value: "#ffffff" },
            // borderRadius: { value: "8px" },
            // padding: { value: "12px 24px" },
            // fontSize: { value: "16px" },
            _hover: {
              backgroundColor: { value: "{colors.brand.primary.80}" },
            },
          },
        },
      },
    },
  };

  return (
    <ThemeProvider theme={customTheme}>
      <Box
        style={{
          maxWidth: "600px",
          margin: "0 auto",
          padding: "20px",
          textAlign: "center",
        }}
      >
        {userImagePath || isNotHuman ? (
          <Box
            style={{
              border: "1px solid #ccc",
              borderRadius: "8px",
              padding: "20px",
              backgroundColor: "#f9f9f9",
              boxShadow: "0 2px 4px rgba(0, 0, 0, 0.1)",
            }}
          >
            {isNotHuman ? (
              <Box style={{ color: "#f30051", marginBottom: "20px" }}>
                <Title
                  order={5}
                  style={{
                    fontWeight: "bold",
                    marginBottom: "10px",
                    color: "red",
                  }}
                  size={"sm"}
                >
                  Unable to verify your identity as a live human face. Please
                  ensure proper lighting and positioning, then try again.
                </Title>
              </Box>
            ) : (
              <Image
                src={getBaseURl() + userImagePath}
                alt="Captured Image"
                style={{
                  maxWidth: "100%",
                  borderRadius: "8px",
                  marginBottom: "20px",
                  height: 350,
                  // minWidth: 100,
                  backgroundColor: "#efefef",
                }}
              />
            )}{" "}
            <Button onClick={fetchCreateLiveness}>Re-take</Button>
            <Text size="xs" c="dimmed" mt={"sm"}>
              *If you'd like to take another photo, please click the 'Retake'
              button.
            </Text>
          </Box>
        ) : loading ? (
          <Loader />
        ) : startCheck && !createLivenessApiData?.sessionId ? (
          <Button onClick={fetchCreateLiveness}>Start Video Check </Button>
        ) : createLivenessApiData?.sessionId ? (
          <FaceLivenessDetector
            sessionId={createLivenessApiData.sessionId}
            region="eu-west-1"
            onAnalysisComplete={handleAnalysisComplete}
            onError={(error) => {
              console.error(error);
            }}
            onUserCancel={() => {
              alert("User cancelled the check");
              setCreateLivenessApiData(null);
              setStartCheck(true);
            }}
          />
        ) : null}
      </Box>
    </ThemeProvider>
  );
}
