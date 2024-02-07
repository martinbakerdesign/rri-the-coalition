import wpPot from "wp-pot";

wpPot({
  destFile: "rri-mapping-tool-dashboard.pot",
  domain: "RRI_MAPPING_TOOL_TEXT_DOMAIN",
  package: "RRI Mapping Tool",
  src: "./*.php",
});
