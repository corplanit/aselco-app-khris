interface StatusPillProps {
  status: string;
}

function slug(status: string): string {
  return status.toLowerCase().replace(/\s+/g, '-');
}

const StatusPill: React.FC<StatusPillProps> = ({ status }) => (
  <span className={`status-pill status-pill--${slug(status)}`}>{status}</span>
);

export default StatusPill;
