// pages/api/agora/token.ts
import type { NextApiRequest, NextApiResponse } from 'next';
import { RtcTokenBuilder, RtcRole } from 'agora-access-token';

const APP_ID = process.env.NEXT_PUBLIC_AGORA_APP_ID!;
const APP_CERT = process.env.AGORA_APP_CERTIFICATE!;
const EXPIRE_SECONDS = parseInt(process.env.TOKEN_EXPIRE_SECONDS || '3600', 10);

export default function handler(req: NextApiRequest, res: NextApiResponse) {
  try {
    const { channel, uid } = req.method === 'POST' ? req.body : req.query;

    if (!channel || channel.length === 0) {
      return res.status(400).json({ error: 'channel is required' });
    }

    // uid can be numeric or string — use 0 for dynamic uid (Agora will assign)
    const numericUid = uid ? Number(uid) : 0;
    const role = RtcRole.PUBLISHER; // or SUBSCRIBER depending on permission

    const currentTimestamp = Math.floor(Date.now() / 1000);
    const privilegeExpire = currentTimestamp + EXPIRE_SECONDS;

    const token = RtcTokenBuilder.buildTokenWithUid(
      APP_ID,
      APP_CERT,
      channel,
      numericUid,
      role,
      privilegeExpire
    );

    return res.status(200).json({ token, appId: APP_ID, channel, uid: numericUid });
  } catch (err: any) {
    console.error('agora token error', err);
    return res.status(500).json({ error: 'token generation failed' });
  }
}
