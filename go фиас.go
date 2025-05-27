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
	"log"
	"os"
	"time"

	_ "github.com/lib/pq"
)

type Object struct {
	ID         string    `xml:"ID,attr"`
	ObjectID   string    `xml:"OBJECTID,attr"`
	ObjectGUID string    `xml:"OBJECTGUID,attr"`
	ChangeID   string    `xml:"CHANGEID,attr"`
	Name       string    `xml:"NAME,attr"`
	TypeName   string    `xml:"TYPENAME,attr"`
	Level      int       `xml:"LEVEL,attr"`
	OperTypeID int       `xml:"OPERTYPEID,attr"`
	PrevID     string    `xml:"PREVID,attr"`
	NextID     string    `xml:"NEXTID,attr"`
	UpdateDate string    `xml:"UPDATEDATE,attr"`
	StartDate  string    `xml:"STARTDATE,attr"`
	EndDate    string    `xml:"ENDDATE,attr"`
	IsActive   int       `xml:"ISACTIVE,attr"`
	IsActual   int       `xml:"ISACTUAL,attr"`
}

const batchSize = 10000

func main() {
	file, err := os.Open("AS_ADDR_OBJ.xml")
	if err != nil {
		log.Fatalf("Ошибка открытия XML: %v", err)
	}
	defer file.Close()

	decoder := xml.NewDecoder(file)
	db, err := sql.Open("postgres", "host=localhost port=5432 user=postgres password=postgres dbname=fias sslmode=disable")
	if err != nil {
		log.Fatalf("Ошибка подключения к БД: %v", err)
	}
	defer db.Close()

	var batch []Object
	count := 0

	for {
		tok, err := decoder.Token()
		if err != nil {
			if err == io.EOF {
				break
			}
			log.Printf("Ошибка чтения токена: %v", err)
			continue
		}
		switch se := tok.(type) {
		case xml.StartElement:
			if se.Name.Local == "OBJECT" {
				var obj Object
				if err := decoder.DecodeElement(&obj, &se); err != nil {
					log.Printf("Ошибка разбора OBJECT: %v", err)
					continue
				}
				batch = append(batch, obj)
				if len(batch) >= batchSize {
					saveBatch(batch, db)
					count += len(batch)
					batch = batch[:0]
					log.Printf("Загружено записей: %d", count)
				}
			}
		}
	}
	if len(batch) > 0 {
		saveBatch(batch, db)
		count += len(batch)
		log.Printf("Финальная загрузка. Всего: %d записей", count)
	}
}

func saveBatch(batch []Object, db *sql.DB) {
	tx, err := db.Begin()
	if err != nil {
		log.Printf("Ошибка транзакции: %v", err)
		return
	}
	stmt, err := tx.Prepare(pq.CopyIn(
		"addr_objects",
		"id", "objectid", "objectguid", "changeid", "name", "typename",
		"level", "opertypeid", "previd", "nextid", "updatedate",
		"startdate", "enddate", "isactive", "isactual"))
	if err != nil {
		tx.Rollback()
		log.Printf("Ошибка подготовки COPY: %v", err)
		return
	}
	for _, obj := range batch {
		_, err := stmt.Exec(
			obj.ID, obj.ObjectID, obj.ObjectGUID, obj.ChangeID, obj.Name, obj.TypeName,
			obj.Level, obj.OperTypeID, obj.PrevID, obj.NextID, obj.UpdateDate,
			obj.StartDate, obj.EndDate, obj.IsActive, obj.IsActual,
		)
		if err != nil {
			log.Printf("Ошибка вставки: %v", err)
		}
	}
	stmt.Exec()
	stmt.Close()
	tx.Commit()
}

---

// schema.sql
CREATE TABLE IF NOT EXISTS addr_objects (
    id TEXT PRIMARY KEY,
    objectid TEXT,
    objectguid TEXT,
    changeid TEXT,
    name TEXT,
    typename TEXT,
    level INTEGER,
    opertypeid INTEGER,
    previd TEXT,
    nextid TEXT,
    updatedate TEXT,
    startdate TEXT,
    enddate TEXT,
    isactive INTEGER,
    isactual INTEGER
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
