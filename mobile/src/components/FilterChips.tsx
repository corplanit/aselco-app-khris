import { IonChip, IonLabel } from '@ionic/react';

interface FilterChipsProps {
  options: string[];
  value: string;
  onChange: (value: string) => void;
}

const FilterChips: React.FC<FilterChipsProps> = ({ options, value, onChange }) => (
  <div style={{ display: 'flex', flexWrap: 'wrap', gap: 6, marginBottom: 12 }}>
    {options.map((opt) => (
      <IonChip
        key={opt}
        color={value === opt ? 'primary' : 'medium'}
        outline={value !== opt}
        onClick={() => onChange(opt)}
      >
        <IonLabel>{opt}</IonLabel>
      </IonChip>
    ))}
  </div>
);

export default FilterChips;
