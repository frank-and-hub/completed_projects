import { NextResponse } from 'next/server';
import type { NextRequest } from 'next/server';
import { getToken } from '@/utils/useAuth';

export async function middleware(req: NextRequest) {
  const token = await getToken();

  if (req.nextUrl.pathname.startsWith('/admin') && !token) {
    return NextResponse.redirect(new URL('/auth/signin', req.url));
  }

  return NextResponse.next();
}