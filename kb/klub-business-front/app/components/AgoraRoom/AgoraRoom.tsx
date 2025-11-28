// components/AgoraRoom.tsx
import React, { useEffect, useState, useRef } from 'react';
import AgoraRTC, { IAgoraRTCClient, ILocalVideoTrack, ILocalAudioTrack } from 'agora-rtc-sdk-ng';
import axios from 'axios';

type Props = {
  channel: string;
  uid?: number | string;
};

export default function AgoraRoom({ channel, uid }: Props) {
  const [joined, setJoined] = useState(false);
  const [remoteUsers, setRemoteUsers] = useState<any[]>([]);
  const clientRef = useRef<IAgoraRTCClient | null>(null);
  const localVideoRef = useRef<HTMLDivElement | null>(null);
  const localAudioTrackRef = useRef<ILocalAudioTrack | null>(null);
  const localVideoTrackRef = useRef<ILocalVideoTrack | null>(null);

  useEffect(() => {
    const appId = process.env.NEXT_PUBLIC_AGORA_APP_ID;
    if (!appId) {
      console.error('Agora APP ID not set in NEXT_PUBLIC_AGORA_APP_ID');
      return;
    }

    const client = AgoraRTC.createClient({ mode: 'rtc', codec: 'vp8' });
    clientRef.current = client;

    // subscribe/unsubscribe handlers
    const handleUserPublished = async (user: any, mediaType: 'audio' | 'video') => {
      await client.subscribe(user, mediaType);
      if (mediaType === 'video') {
        const remoteVideoTrack = user.videoTrack;
        const container = document.createElement('div');
        container.id = `player-${user.uid}`;
        container.style.width = '320px';
        container.style.height = '240px';
        document.getElementById('remote-container')?.appendChild(container);
        remoteVideoTrack.play(container);
      }
      if (mediaType === 'audio') {
        const remoteAudioTrack = user.audioTrack;
        remoteAudioTrack.play();
      }
      setRemoteUsers((prev) => [...prev, user]);
    };

    const handleUserLeft = (user: any) => {
      setRemoteUsers((prev) => prev.filter((u) => u.uid !== user.uid));
      const el = document.getElementById(`player-${user.uid}`);
      if (el) el.remove();
    };

    client.on('user-published', handleUserPublished);
    client.on('user-left', handleUserLeft);

    return () => {
      client.off('user-published', handleUserPublished);
      client.off('user-left', handleUserLeft);
    };
  }, []);

  const join = async () => {
    try {
      // get token from server
      const resp = await axios.post('/api/agora/token', { channel, uid });
      const { token, appId, uid: returnedUid } = resp.data;

      const client = clientRef.current!;
      // request media permission and create tracks
      const localAudioTrack = await AgoraRTC.createMicrophoneAudioTrack();
      const localVideoTrack = await AgoraRTC.createCameraVideoTrack();

      localAudioTrackRef.current = localAudioTrack;
      localVideoTrackRef.current = localVideoTrack;

      await client.join(appId, channel, token, returnedUid || 0);
      await client.publish([localAudioTrack, localVideoTrack]);

      // play local video
      if (localVideoRef.current) {
        localVideoTrack.play(localVideoRef.current);
      }

      setJoined(true);
    } catch (err) {
      console.error('join error', err);
      alert('Failed to join the room. Check console for details.');
    }
  };

  const leave = async () => {
    const client = clientRef.current!;
    if (localAudioTrackRef.current) {
      localAudioTrackRef.current.stop();
      localAudioTrackRef.current.close();
      localAudioTrackRef.current = null;
    }
    if (localVideoTrackRef.current) {
      localVideoTrackRef.current.stop();
      localVideoTrackRef.current.close();
      localVideoTrackRef.current = null;
    }

    await client.unpublish();
    await client.leave();

    // cleanup remote containers
    const remoteContainer = document.getElementById('remote-container');
    if (remoteContainer) remoteContainer.innerHTML = '';

    setJoined(false);
    setRemoteUsers([]);
  };

  return (
    <div>
      <div>
        <button onClick={join} disabled={joined}>Join</button>
        <button onClick={leave} disabled={!joined}>Leave</button>
      </div>

      <div style={{ display: 'flex', gap: 12, marginTop: 12 }}>
        <div>
          <h4>Local</h4>
          <div
            ref={localVideoRef}
            id="local-player"
            style={{ width: 320, height: 240, background: '#000' }}
          />
        </div>

        <div>
          <h4>Remote</h4>
          <div id="remote-container" style={{ display: 'flex', gap: 8, flexWrap: 'wrap' }} />
        </div>
      </div>
    </div>
  );
}
