import { useRef } from "react";
import {
  IconCheck,
  IconCircleX,
  IconCloudUpload,
  IconCross,
  IconDownload,
  IconFile,
  IconFile3d,
  IconFileTypePdf,
  IconPaperclip,
  IconX,
} from "@tabler/icons-react";
import {
  Button,
  Center,
  Group,
  Progress,
  Text,
  useMantineTheme,
} from "@mantine/core";
import { Dropzone, MIME_TYPES } from "@mantine/dropzone";
import "./uploadContract.scss";
import useUploadContract from "./useUploadContract";

export function UploadContract({
  handleClose,
  queryKey,
  contract_id,
  admin_id,
}: any) {
  const theme = useMantineTheme();
  const openRef = useRef<() => void>(null);
  const { handleDrop, file, uploadFile, progress, uploaded, uploading } =
    useUploadContract(handleClose, queryKey, contract_id, admin_id);

  return (
    <div className={"wrapper"}>
      <Dropzone
        openRef={openRef}
        onDrop={handleDrop}
        className={"dropzone"}
        radius="md"
        accept={[MIME_TYPES.pdf]}
        maxSize={10 * 1024 ** 2}
        multiple={false}
      >
        <button
          onClick={(event) => {
            event.stopPropagation(); // Prevent Dropzone from handling the click
            handleClose && handleClose();
          }}
          style={{
            pointerEvents: "auto",
          }}
        >
          <IconCircleX
            size={28}
            style={{
              position: "absolute",
              right: 20,
              zIndex: 99,
              pointerEvents: "auto",
            }}
          />
        </button>
        <div style={{ pointerEvents: "none" }}>
          <Group justify="center">
            <Dropzone.Accept>
              <IconDownload
                size={50}
                color={theme.colors.blue[6]}
                stroke={1.5}
              />
            </Dropzone.Accept>
            <Dropzone.Reject>
              <IconX size={50} color={theme.colors.red[6]} stroke={1.5} />
            </Dropzone.Reject>
            <Dropzone.Idle>
              {file ? (
                <IconPaperclip size={50} />
              ) : (
                <IconCloudUpload size={50} stroke={1.5} />
              )}
            </Dropzone.Idle>
          </Group>

          <Text
            ta="center"
            fw={700}
            fz="lg"
            mt={file ? "sm" : "xl"}
            style={{ wordWrap: "break-word" }}
            lineClamp={4}
          >
            <Dropzone.Accept>Drop files here</Dropzone.Accept>
            <Dropzone.Reject>Pdf file less than 10mb</Dropzone.Reject>
            <Dropzone.Idle>
              {file ? file.name : "Upload Contract"}
            </Dropzone.Idle>
          </Text>
          <Text ta="center" fz="sm" mt="xs" c="dimmed">
            Drag&apos;n&apos;drop files here to upload. We can accept only{" "}
            <i>.pdf</i> files that are less than 10mb in size.
          </Text>
        </div>
        <div
          style={{
            position: "absolute",
            width: "100%",
            bottom: "-2px",
            left: 0,
            // marginBottom: 20,
          }}
        >
          {file && (
            <div
              className="progress-upload"
              style={{ width: "100%" }}
              // style={{ width: "100%", height: 10, background: "red" }}
            >
              <Progress
                value={progress}
                size="sm"
                mt="xs"
                color={uploaded ? "green" : "blue"}
              />
            </div>
          )}

          <Button
            className="control"
            style={{
              pointerEvents: "auto",
            }}
            disabled={uploading}
            size="md"
            // radius="xl"
            onClick={(event) => {
              event.stopPropagation(); // Prevent Dropzone from opening
              if (file) {
                uploadFile();
              } else {
                openRef.current?.();
              }
            }}
          >
            {uploading ? "Uploading..." : file ? "Upload file" : "Select file"}
          </Button>
        </div>
      </Dropzone>

      <div style={{ height: 20 }} />
    </div>
  );
}
