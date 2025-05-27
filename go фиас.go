module github.com/yourusername/fias_xml_importer

require (
	github.com/lib/pq v1.10.9
)

---

// main.go
package main

import (
	"database/sql"
	"encoding/xml"
	"fmt"
	"io"
	"os"

	_ "github.com/lib/pq"
)

type Object struct {
	ID     string `xml:"ID,attr"`
	Name   string `xml:"NAME,attr"`
	Type   string `xml:"TYPENAME,attr"`
	Level  int    `xml:"AOLEVEL,attr"`
	Region string `xml:"REGIONCODE,attr"`
}

func main() {
	file, err := os.Open("AS_ADDR_OBJ.xml")
	if err != nil {
		panic(err)
	}
	defer file.Close()

	decoder := xml.NewDecoder(file)
	connStr := "host=localhost port=5432 user=postgres password=postgres dbname=fias sslmode=disable"
	db, err := sql.Open("postgres", connStr)
	if err != nil {
		panic(err)
	}
	defer db.Close()

	for {
		tok, err := decoder.Token()
		if err != nil {
			if err == io.EOF {
				break
			}
			panic(err)
		}

		switch se := tok.(type) {
		case xml.StartElement:
			if se.Name.Local == "OBJECT" {
				var obj Object
				err := decoder.DecodeElement(&obj, &se)
				if err != nil {
					fmt.Printf("Ошибка декодирования элемента: %v\n", err)
					continue
				}
				saveToDB(obj, db)
			}
		}
	}
	fmt.Println("Импорт завершён.")
}

func saveToDB(obj Object, db *sql.DB) {
	_, err := db.Exec(`
		INSERT INTO addr_objects (id, name, typename, aolevel, regioncode)
		VALUES ($1, $2, $3, $4, $5)
		ON CONFLICT (id) DO NOTHING
	`, obj.ID, obj.Name, obj.Type, obj.Level, obj.Region)
	if err != nil {
		fmt.Printf("Ошибка сохранения в БД: %v\n", err)
	}
}

---

// schema.sql
CREATE TABLE IF NOT EXISTS addr_objects (
    id TEXT PRIMARY KEY,
    name TEXT,
    typename TEXT,
    aolevel INTEGER,
    regioncode TEXT
);

---

# Dockerfile
FROM golang:1.21

WORKDIR /app

COPY go.mod .
COPY go.sum .
RUN go mod download

COPY . .

RUN go build -o fias_xml_importer

CMD ["./fias_xml_importer"]

---

# Makefile
run:
	go run main.go

build:
	go build -o fias_xml_importer

docker-build:
	docker build -t fias_importer .

docker-run:
	docker run -v $(PWD):/app -w /app --network=host fias_importer
