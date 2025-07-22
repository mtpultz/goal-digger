import React, { useState } from "react";
import { Link } from "react-router-dom";
import { Button } from "./ui/button";

export interface GoalTreeNode {
    id: number;
    title: string;
    status: string;
    description?: string;
    created_at: string;
    target_date?: string;
    children?: GoalTreeNode[];
}

interface TreeViewProps {
    node: GoalTreeNode;
    level?: number;
    selectedId?: number;
}

const getStatusColor = (status: string): string => {
    switch (status) {
        case "COMPLETE":
            return "bg-green-100 text-green-800";
        case "ACTIVE":
            return "bg-blue-100 text-blue-800";
        case "OPEN":
            return "bg-gray-100 text-gray-800";
        case "SKIPPED":
            return "bg-yellow-100 text-yellow-800";
        default:
            return "bg-gray-100 text-gray-800";
    }
};

const getStatusLabel = (status: string): string => {
    switch (status) {
        case "COMPLETE":
            return "Complete";
        case "ACTIVE":
            return "Active";
        case "OPEN":
            return "Open";
        case "SKIPPED":
            return "Skipped";
        default:
            return status;
    }
};

export const TreeView: React.FC<TreeViewProps> = ({
    node,
    level = 0,
    selectedId,
}) => {
    const [expanded, setExpanded] = useState<boolean>(true);
    const isSelected = node.id === selectedId;
    const hasChildren = node.children && node.children.length > 0;

    return (
        <div style={{ marginLeft: level * 24 }} className="mb-2">
            <div
                className={`flex items-center p-3 rounded-lg border transition-colors ${
                    isSelected
                        ? "bg-blue-50 border-blue-200"
                        : "bg-white border-gray-200 hover:bg-gray-50"
                }`}
            >
                {hasChildren && (
                    <Button
                        variant="ghost"
                        size="sm"
                        onClick={() => setExpanded((e) => !e)}
                        className="mr-2 p-1 h-6 w-6"
                        tabIndex={-1}
                    >
                        {expanded ? "▼" : "▶"}
                    </Button>
                )}
                <div className="flex-1">
                    <div className="flex items-center justify-between">
                        <div className="flex items-center space-x-3">
                            <h3
                                className={`font-medium ${
                                    isSelected
                                        ? "text-blue-900"
                                        : "text-gray-900"
                                }`}
                            >
                                {node.title}
                            </h3>
                            <span
                                className={`px-2 py-1 rounded-full text-xs font-medium ${getStatusColor(
                                    node.status
                                )}`}
                            >
                                {getStatusLabel(node.status)}
                            </span>
                        </div>
                        <Link
                            to={`/goals/${node.id}`}
                            className="text-blue-600 hover:text-blue-700 text-sm font-medium"
                        >
                            View Details
                        </Link>
                    </div>
                    {node.description && (
                        <p className="text-gray-600 text-sm mt-1 line-clamp-2">
                            {node.description}
                        </p>
                    )}
                    <div className="flex items-center space-x-4 text-xs text-gray-500 mt-2">
                        <span>
                            Created:{" "}
                            {new Date(node.created_at).toLocaleDateString()}
                        </span>
                        {node.target_date && (
                            <span>
                                Target:{" "}
                                {new Date(
                                    node.target_date
                                ).toLocaleDateString()}
                            </span>
                        )}
                        {hasChildren && (
                            <span>
                                {node.children!.length} sub-goal
                                {node.children!.length !== 1 ? "s" : ""}
                            </span>
                        )}
                    </div>
                </div>
            </div>
            {hasChildren && expanded && (
                <div className="mt-2">
                    {node.children!.map((child) => (
                        <TreeView
                            key={child.id}
                            node={child}
                            level={level + 1}
                            selectedId={selectedId}
                        />
                    ))}
                </div>
            )}
        </div>
    );
};

export default TreeView;
