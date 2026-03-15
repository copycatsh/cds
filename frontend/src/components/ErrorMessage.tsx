import { ApiError } from '../api';

interface Props {
  error: Error | null;
}

export default function ErrorMessage({ error }: Props) {
  if (!error) return null;

  const message = error instanceof ApiError
    ? error.message
    : 'An unexpected error occurred';

  return (
    <div className="rounded-md bg-red-50 border border-red-200 p-4">
      <p className="text-sm text-red-700">{message}</p>
    </div>
  );
}
