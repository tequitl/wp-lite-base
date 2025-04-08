import Click from './click.js';
import SelectChange from './select-change.js';
import Focus from './focus.js';

export default function eventsLoader(jq, forms) {
  Click(jq, forms);
  SelectChange(jq, forms);
  Focus(jq, forms);
}