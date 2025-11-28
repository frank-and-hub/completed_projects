export class LogEntity {
    id: string;
    model?: string | null;
    action: string;
    query: string;
    durationMs: number;
    createdAt: Date;
}
