import * as argon from 'argon2';

export function generateRandomString(length: number = 10): string {
  const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
  let result = '';
  for (let i = 0; i < length; i++) {
    result += chars.charAt(Math.floor(Math.random() * chars.length));
  }
  return result;
}

export function generateRandomNumber(length: number = 10): string {
  const chars = '0123456789';
  let result = '';
  for (let i = 0; i < length; i++) {
    result += chars.charAt(Math.floor(Math.random() * chars.length));
  }
  return result;
}

export function sleep(ms: number) {
  console.log(`sleep for ${ms / 1000} sec`)
  return new Promise(resolve => setTimeout(resolve, ms));
}

export function getRandomNumberGreaterThan(max: number = 15): number {
  return Math.floor(Math.random() * (max - 10)) + 10;
}

export function capitalize(str: string): string {
  if (!str) return '';
  return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
}

export function getRandomBoolean(): boolean {
  return Math.random() < 0.5;
}

export function shuffleArray<T>(array: T[]): T[] {
  return array.sort(() => Math.random() - 0.5);
}

export function getRandomElement<T>(array: T[]): T {
  return array[Math.floor(Math.random() * array.length)];
}

export function getRandomDate(start: Date, end: Date): Date {
  return new Date(start.getTime() + Math.random() * (end.getTime() - start.getTime()));
}

export function slugify(text: string): string {
  return text
    .toString()
    .toLowerCase()
    .trim()
    .replace(/\s+/g, '-')
    .replace(/[^\w\-]+/g, '')
    .replace(/\-\-+/g, '-');
}

export function generateFakeEmail(firstName: string, lastName: string): string {
  const domain = getRandomElement(['gmail.com', 'yahoo.com', 'outlook.com', 'yopmail.com']);
  return `${firstName.toLowerCase()}.${lastName.toLowerCase()}@${domain}`;
}

export function generateRandomPhoneNumber(): string {
  return '+1' + generateRandomNumber(10);
}

export function generateRandomPassword(length: number = 12): string {
  const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()';
  return Array.from({ length }, () => chars[Math.floor(Math.random() * chars.length)]).join('');
}

export function toISODate(date: Date): string {
  return date.toISOString();
}

export function getCurrentTimestamp(): number {
  return Date.now();
}

export function padNumber(num: number, length: number): string {
  return num.toString().padStart(length, '0');
}

export function truncateText(text: string, maxLength: number): string {
  return text.length > maxLength ? `${text.slice(0, maxLength)}...` : text;
}

export function isValidEmail(email: string): boolean {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

export function repeat<T>(times: number, fn: (index: number) => T): T[] {
  return Array.from({ length: times }, (_, i) => fn(i));
}

export async function forEachAsync<T>(
  array: T[],
  callback: (item: T, index: number) => Promise<void>
): Promise<void> {
  for (let i = 0; i < array.length; i++) {
    await callback(array[i], i);
  }
}

export async function mapAsync<T, R>(
  array: T[],
  callback: (item: T, index: number) => Promise<R>
): Promise<R[]> {
  const results: R[] = [];
  for (let i = 0; i < array.length; i++) {
    results.push(await callback(array[i], i));
  }
  return results;
}

export async function sleepEach<T>(
  array: T[],
  ms: number,
  callback: (item: T, index: number) => Promise<void>
) {
  for (let i = 0; i < array.length; i++) {
    await callback(array[i], i);
    await sleep(ms);
  }
}

export async function generatePassword(string: string): Promise<string> {
  return await argon.hash(string);
}

export function generateRandomParagraph(sentenceCount: number = 5): string {
  let paragraph = '';
  for (let i = 0; i < sentenceCount; i++) {
    const wordCount = Math.floor(Math.random() * 10) + 5; // 5–15 words per sentence
    const sentenceWords: string[] = [];

    for (let j = 0; j < wordCount; j++) {
      const wordLength = Math.floor(Math.random() * 10) + 5; // words of 5–15 characters
      sentenceWords.push(generateRandomString(wordLength));
    }

    let sentence = sentenceWords.join(' ') + '.';
    sentence = sentence.charAt(0).toUpperCase() + sentence.slice(1); // Capitalize
    paragraph += sentence + ' ';
  }

  return paragraph.trim();
}