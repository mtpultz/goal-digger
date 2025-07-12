import React from "react";
import { Link } from "react-router-dom";
import { Button } from "../components/ui/button";
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from "../components/ui/card";
import { useAuth } from "../contexts/AuthContext";

const Home: React.FC = () => {
    const { isAuthenticated } = useAuth();

    return (
        <div className="text-center">
            <div className="max-w-4xl mx-auto">
                <h1 className="text-5xl font-bold text-gray-900 mb-6">
                    Welcome to Goal Digger
                </h1>
                <p className="text-xl text-gray-600 mb-8">
                    A goal tracker for those chasing goals like they're rich,
                    shiny, and mildly afraid of commitment.
                </p>

                <Card className="mb-8">
                    <CardHeader>
                        <CardTitle>Why Goal Digger?</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid md:grid-cols-3 gap-6 text-left">
                            <div className="text-center">
                                <div className="bg-blue-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                                    <span className="text-2xl">🎯</span>
                                </div>
                                <h3 className="font-semibold text-gray-800 mb-2">
                                    Set Clear Goals
                                </h3>
                                <p className="text-gray-600">
                                    Define your objectives with clarity and
                                    purpose
                                </p>
                            </div>
                            <div className="text-center">
                                <div className="bg-green-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                                    <span className="text-2xl">📈</span>
                                </div>
                                <h3 className="font-semibold text-gray-800 mb-2">
                                    Track Progress
                                </h3>
                                <p className="text-gray-600">
                                    Monitor your advancement with detailed
                                    insights
                                </p>
                            </div>
                            <div className="text-center">
                                <div className="bg-purple-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                                    <span className="text-2xl">🤝</span>
                                </div>
                                <h3 className="font-semibold text-gray-800 mb-2">
                                    Get Support
                                </h3>
                                <p className="text-gray-600">
                                    Connect with buddies to stay motivated
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {isAuthenticated ? (
                    <div className="space-y-4">
                        <Button asChild size="lg">
                            <Link to="/goals">View My Goals</Link>
                        </Button>
                    </div>
                ) : (
                    <div className="space-x-4">
                        <Button asChild size="lg">
                            <Link to="/register">Get Started</Link>
                        </Button>
                        <Button asChild variant="outline" size="lg">
                            <Link to="/login">Sign In</Link>
                        </Button>
                    </div>
                )}
            </div>
        </div>
    );
};

export default Home;
