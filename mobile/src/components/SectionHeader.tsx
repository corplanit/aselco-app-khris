import { Link } from 'react-router-dom';

interface SectionHeaderProps {
  title: string;
  linkLabel?: string;
  linkHref?: string;
}

const SectionHeader: React.FC<SectionHeaderProps> = ({ title, linkLabel, linkHref }) => (
  <div className="section">
    <div className="section__head">
      <h2 className="section__title" style={{ margin: 0 }}>
        {title}
      </h2>
      {linkLabel && linkHref && (
        <Link className="section__link" to={linkHref}>
          {linkLabel}
        </Link>
      )}
    </div>
  </div>
);

export default SectionHeader;
