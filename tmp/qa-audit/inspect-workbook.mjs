import fs from "node:fs/promises";
import { FileBlob, SpreadsheetFile } from "@oai/artifact-tool";

const inputPath = "C:/Users/Pangod97/Desktop/pruebas de sistema CEOT.xlsx";
const outputDir = "C:/laragon/www/llamaDates/tmp/qa-audit/workbook-renders";

await fs.mkdir(outputDir, { recursive: true });
const input = await FileBlob.load(inputPath);
const workbook = await SpreadsheetFile.importXlsx(input);

const sheets = await workbook.inspect({ kind: "sheet", include: "id,name", maxChars: 12000 });
console.log("SHEETS");
console.log(sheets.ndjson);

const summary = await workbook.inspect({
  kind: "workbook,sheet,table,region",
  maxChars: 50000,
  tableMaxRows: 100,
  tableMaxCols: 30,
  tableMaxCellChars: 500,
});
console.log("SUMMARY");
console.log(summary.ndjson);

const sheetLines = sheets.ndjson.split(/\r?\n/).filter(Boolean).map((line) => JSON.parse(line));
for (const item of sheetLines) {
  const sheetName = item.name;
  if (!sheetName) continue;
  const detail = await workbook.inspect({
    kind: "region,table",
    sheetId: sheetName,
    range: "A1:AZ500",
    maxChars: 100000,
    tableMaxRows: 500,
    tableMaxCols: 52,
    tableMaxCellChars: 1000,
  });
  console.log(`DETAIL:${sheetName}`);
  console.log(detail.ndjson);

  const preview = await workbook.render({ sheetName, autoCrop: "all", scale: 1, format: "png" });
  const safeName = sheetName.replace(/[^a-zA-Z0-9_-]+/g, "_");
  await fs.writeFile(`${outputDir}/${safeName}.png`, new Uint8Array(await preview.arrayBuffer()));
}
