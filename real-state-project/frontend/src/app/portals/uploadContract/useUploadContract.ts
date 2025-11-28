import { uploadContract } from "@/api/subscription/subscription";
import { useAppSelector } from "@/store/hooks";
import { notification } from "@/utils/notification";
import { useQueryClient } from "@tanstack/react-query";
import axios from "axios";
import { useState } from "react";

function useUploadContract(
  handleClose: any,
  queryKey: any,
  contract_id: any,
  admin_id: any
) {
  const queryClient = useQueryClient();
  const userId = useAppSelector((state) => state?.userReducer?.userDetail?.id);
  const [file, setFile] = useState<File | null>(null);
  const [progress, setProgress] = useState(0);
  const [uploading, setUploading] = useState(false);
  const [uploaded, setUploaded] = useState(false);
  const handleDrop = (files: File[]) => {
    if (files.length > 0) {
      setFile(files[0]);
      setProgress(0);
      setUploaded(false);
    }
  };

  const uploadFile = async () => {
    if (!file) return;
    setUploading(true);
    setUploaded(false);

    const formData = new FormData();
    formData.append("file", file);
    formData.append("user_id", userId!);
    formData.append("contract_id", contract_id);
    formData.append("admin_id", admin_id);

    try {
      await uploadContract(formData, {
        onUploadProgress: (progressEvent) => {
          if (progressEvent.total) {
            setProgress(
              Math.round((progressEvent.loaded * 100) / progressEvent.total)
            );
          }
        },
      });
      // await axios.post("https://api.pdfrest.com/upload", formData, {
      //   headers: {
      //     "Content-Type": "multipart/form-data",
      //     "Api-Key": "81f974e3-a799-47d2-884f-2cddae9e37f6",
      //   },
      //   onUploadProgress: (progressEvent) => {
      //     if (progressEvent.total) {
      //       setProgress(
      //         Math.round((progressEvent.loaded * 100) / progressEvent.total)
      //       );
      //     }
      //   },
      // });
      await queryClient.invalidateQueries({
        queryKey,
      });
      setUploaded(true);
      notification({
        message: "File uploaded successfully.",
        type: "success",
      });
      handleClose && handleClose();
    } catch (error) {
      console.error("Upload failed", error);
      setProgress(0);
    } finally {
      setUploading(false);
    }
  };

  return { handleDrop, file, uploadFile, uploading, progress, uploaded };
}

export default useUploadContract;
