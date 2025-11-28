import React, { useEffect, useRef, useState } from 'react'

export const CaptureImage = () => {
    const videoRef = useRef(null);
    const canvasRef = useRef(null);
    const [image, setImage] = useState(null);
    const [stream, setStream] = useState(null);
    // const [isVideoReady, setIsVideoReady] = useState(false);

    // useEffect(() => {
    //     navigator.mediaDevices
    //         .getUserMedia({ video: true })
    //         .then((stream) => {
    //             videoRef.current.srcObject = stream;
    //             setStream(stream);
    //         })
    //         .catch((error) => {
    //             console.error("Error accessing the webcam: ", error);
    //         });
    //     return () => {
    //         const stream = videoRef.current?.srcObject;
    //         if (stream) {
    //             const tracks = stream.getTracks();
    //             tracks.forEach((track) => track.stop());
    //         }
    //     };
    // }, []);

    useEffect(() => {
        // Ensure that video is accessible and ready for use
        const startVideoStream = async () => {
            try {
                const userMediaStream = await navigator.mediaDevices.getUserMedia({ video: true });
                videoRef.current.srcObject = userMediaStream;
                setStream(userMediaStream);

                // Listen to when the video is ready
                videoRef.current.onloadedmetadata = () => {
                    // setIsVideoReady(true); // Video is ready for capture
                };
            } catch (error) {
                console.error("Error accessing the webcam: ", error);
            }
        };

        startVideoStream();

        return () => {
            // Cleanup the stream when component is unmounted
            const stream = videoRef.current?.srcObject;
            if (stream) {
                const tracks = stream.getTracks();
                tracks.forEach((track) => track.stop());
            }
        };
    }, []);

    const captureImage = () => {
        const canvas = canvasRef.current;
        const ctx = canvas.getContext('2d');

        const width = videoRef.current.videoWidth;
        const height = videoRef.current.videoHeight;
        canvas.width = width;
        canvas.height = height;

        ctx.drawImage(videoRef.current, 0, 0, width, height);
        const dataUrl = canvas.toDataURL('image/png');

        setImage(dataUrl);
        if (stream) {
            const tracks = stream.getTracks();
            tracks.forEach((track) => track.stop());
        }
    };

    return (
        <>
            {!image && <div className={`p-2 rounded-50`}>
                <video
                    ref={videoRef}
                    autoPlay
                    playsInline
                    className={`rounded-circle circle-image-xl video-cap`}
                    style={{ border: '1px solid black' }}
                ></video>
                <button onClick={captureImage} className={`col-12`}>Capture Image</button>
                <canvas ref={canvasRef} style={{ display: 'none' }}></canvas>
            </div>}
            {image && <center className={`p-2 rounded-50`}>
                <img src={image} alt="Captured" className={`rounded-circle circle-image-xl `} />
            </center>}
        </>
    )
}
